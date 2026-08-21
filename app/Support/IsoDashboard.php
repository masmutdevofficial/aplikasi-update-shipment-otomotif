<?php

namespace App\Support;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class IsoDashboard
{
    /** @return array<int, string> */
    public static function positions(string $type): array
    {
        return $type === 'iso-laut'
            ? ['DO Received', 'Pickup', 'Storage Port', 'Kapal (Loading)', 'Kapal (Aboard)', 'Storage Port (Destination)', 'PTD/DTD']
            : ['DO Received', 'Pickup', 'PTD/DTD'];
    }

    /**
     * @return array<int, array{destination: string, total: int, positions: array<string, array{count: int, percentage: float|int}>}>
     */
    public static function positionSummary(string $type, ?int $month = null, ?int $year = null): array
    {
        return self::periodQuery($type, $month, $year)
            ->whereNotNull('terima_do')
            ->get()
            ->groupBy(fn (Model $shipment) => self::normalizedDestination($shipment->destination))
            ->reject(fn ($shipments, string $destination) => $destination === '')
            ->map(function ($shipments, string $destination) use ($type) {
                $total = $shipments->count();
                $counts = $shipments->countBy(fn (Model $shipment) => self::currentPosition($type, $shipment));
                $positions = [];

                foreach (self::positions($type) as $position) {
                    $count = (int) $counts->get($position, 0);
                    $positions[$position] = [
                        'count' => $count,
                        'percentage' => $total === 0 ? 0 : round($count / $total * 100, 2),
                    ];
                }

                return ['destination' => $destination, 'total' => $total, 'positions' => $positions];
            })
            ->sortBy('destination')
            ->values()
            ->all();
    }

    /** @return array<int, array{city: string, total: int, otd: int, late: int, percentage: float|int}> */
    public static function lateByDestination(?int $month = null, ?int $year = null): array
    {
        return self::periodQuery('iso-laut', $month, $year)
            ->whereNotNull('terima_do')
            ->get()
            ->groupBy(fn (IsoLautShipment $shipment) => self::normalizedDestination($shipment->destination))
            ->reject(fn ($shipments, string $destination) => $destination === '')
            ->map(function ($shipments, string $destination) {
                $results = $shipments
                    ->map(fn (IsoLautShipment $shipment) => SpecialShipmentPerformance::calculate('iso-laut', $shipment)['sla_result'])
                    ->filter(fn (string $result) => in_array($result, ['OTD', 'LATE'], true));
                $total = $results->count();
                $late = $results->filter(fn (string $result) => $result === 'LATE')->count();

                return [
                    'city' => $destination,
                    'total' => $total,
                    'otd' => $total - $late,
                    'late' => $late,
                    'percentage' => $total === 0 ? 0 : round($late / $total * 100, 2),
                ];
            })
            ->filter(fn (array $summary) => $summary['total'] > 0)
            ->sortBy('city')
            ->values()
            ->all();
    }

    /** @return array<string, array{count: int, percentage: float|int}> */
    public static function doPerformanceStatistics(?int $month = null, ?int $year = null): array
    {
        $shipments = self::periodQuery('iso-laut', $month, $year)
            ->whereNotNull('terima_do')
            ->get();
        $total = $shipments->count();
        $counts = [
            'total_received' => $total,
            'not_departed_pdc' => $shipments->whereNull('keluar_dari_pdc')->count(),
            'departed_pdc' => $shipments->whereNotNull('keluar_dari_pdc')->count(),
            'storage_port' => $shipments->whereNotNull('at_storage_port')->count(),
            'vessel_loading' => $shipments->whereNotNull('atd_kapal_loading')->count(),
            'vessel_arrived' => $shipments->whereNotNull('ata_kapal')->count(),
            'destination_storage' => $shipments->whereNotNull('ata_storage_port_destination')->count(),
            'ptd_dtd' => $shipments->filter(fn (IsoLautShipment $shipment) => self::hasValue($shipment->at_ptd_dtd))->count(),
        ];

        return collect($counts)
            ->map(fn (int $count) => [
                'count' => $count,
                'percentage' => $total === 0 ? 0 : round($count / $total * 100, 2),
            ])
            ->all();
    }

    /**
     * @return array{
     *     origin: array<int, array{city: string, average: float|int, minimum: int, maximum: int}>,
     *     destination: array<int, array{city: string, average: float|int, minimum: int, maximum: int}>
     * }
     */
    public static function dwellingDetails(?int $month = null, ?int $year = null): array
    {
        $shipments = self::periodQuery('iso-laut', $month, $year)->get();

        return [
            'origin' => self::dwellingRows($shipments, 'lead_time_loading'),
            'destination' => self::dwellingRows($shipments, 'lead_time_ptd_dtd'),
        ];
    }

    private static function dwellingRows(Collection $shipments, string $metric): array
    {
        return $shipments
            ->map(function (IsoLautShipment $shipment) use ($metric) {
                $days = SpecialShipmentPerformance::calculate('iso-laut', $shipment)[$metric];

                return [
                    'city' => self::normalizedDestination($shipment->destination),
                    'days' => $days,
                ];
            })
            ->filter(fn (array $row) => $row['city'] !== '' && $row['days'] !== null)
            ->groupBy('city')
            ->map(fn (Collection $rows, string $city) => [
                'city' => $city,
                'average' => round((float) $rows->avg('days'), 2),
                'minimum' => (int) $rows->min('days'),
                'maximum' => (int) $rows->max('days'),
            ])
            ->sortBy('city')
            ->values()
            ->all();
    }

    private static function currentPosition(string $type, Model $shipment): string
    {
        if ($type === 'iso-darat') {
            return match (true) {
                self::hasValue($shipment->at_ptd_dtd) => 'PTD/DTD',
                $shipment->keluar_dari_pdc !== null => 'Pickup',
                default => 'DO Received',
            };
        }

        return match (true) {
            self::hasValue($shipment->at_ptd_dtd) => 'PTD/DTD',
            $shipment->ata_storage_port_destination !== null => 'Storage Port (Destination)',
            $shipment->ata_kapal !== null => 'Kapal (Aboard)',
            $shipment->atd_kapal_loading !== null => 'Kapal (Loading)',
            $shipment->at_storage_port !== null => 'Storage Port',
            $shipment->keluar_dari_pdc !== null => 'Pickup',
            default => 'DO Received',
        };
    }

    private static function periodQuery(string $type, ?int $month, ?int $year): Builder
    {
        $model = $type === 'iso-laut' ? IsoLautShipment::class : IsoDaratShipment::class;

        return $model::query()
            ->when($month !== null, fn (Builder $query) => $query->whereMonth('terima_do', $month))
            ->when($year !== null, fn (Builder $query) => $query->whereYear('terima_do', $year));
    }

    private static function normalizedDestination(?string $destination): string
    {
        return strtoupper(trim((string) $destination));
    }

    private static function hasValue(mixed $value): bool
    {
        $text = strtoupper(trim((string) $value));

        return $text !== '' && ! str_contains($text, 'VALUE');
    }
}
