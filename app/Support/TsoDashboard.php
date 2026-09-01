<?php

namespace App\Support;

use App\Models\TsoShipment;
use Illuminate\Database\Eloquent\Builder;

class TsoDashboard
{
    /** @return array<int, string> */
    public static function positions(): array
    {
        return [
            'DO Received',
            'Pickup',
            'Door to Port',
            'Port to Port',
            'Port to Door',
        ];
    }

    /**
     * Destination diambil langsung dari data sehingga destination baru
     * otomatis muncul tanpa daftar kota statis.
     *
     * @return array<int, array{destination: string, total: int, positions: array<string, array{count: int, percentage: float|int}>}>
     */
    public static function positionSummary(?int $month = null, ?int $year = null, ?int $day = null): array
    {
        return self::periodQuery($month, $year, $day)
            ->whereNotNull('do_date')
            ->get()
            ->groupBy(fn (TsoShipment $shipment) => self::normalizedDestination($shipment->destination))
            ->reject(fn ($shipments, string $destination) => $destination === '')
            ->map(function ($shipments, string $destination) {
                $total = $shipments->count();
                $counts = $shipments->countBy(fn (TsoShipment $shipment) => self::currentPosition($shipment));
                $positions = [];

                foreach (self::positions() as $position) {
                    $count = (int) $counts->get($position, 0);
                    $positions[$position] = [
                        'count' => $count,
                        'percentage' => $total === 0 ? 0 : round($count / $total * 100, 2),
                    ];
                }

                return [
                    'destination' => $destination,
                    'total' => $total,
                    'positions' => $positions,
                ];
            })
            ->sortBy('destination')
            ->values()
            ->all();
    }

    /** @return array<string, array{count: int, percentage: float|int}> */
    public static function doPerformanceStatistics(?int $month = null, ?int $year = null, ?int $day = null): array
    {
        $shipments = self::periodQuery($month, $year, $day)
            ->whereNotNull('do_date')
            ->get();
        $total = $shipments->count();
        $positionCounts = $shipments->countBy(fn (TsoShipment $shipment) => self::ddPerformancePosition($shipment));
        $counts = [
            'total_received' => $total,
            'not_departed_pdc' => (int) $positionCounts->get('not_departed_pdc', 0),
            'dtp' => (int) $positionCounts->get('dtp', 0),
            'ptp' => (int) $positionCounts->get('ptp', 0),
            'ptd' => (int) $positionCounts->get('ptd', 0),
        ];

        return collect($counts)
            ->map(fn (int $count) => [
                'count' => $count,
                'percentage' => $total === 0 ? 0 : round($count / $total * 100, 2),
            ])
            ->all();
    }

    private static function ddPerformancePosition(TsoShipment $shipment): string
    {
        return match (true) {
            $shipment->port_to_door !== null => 'ptd',
            $shipment->port_to_port !== null => 'ptp',
            $shipment->pu_date !== null => 'dtp',
            default => 'not_departed_pdc',
        };
    }

    private static function currentPosition(TsoShipment $shipment): string
    {
        return match (true) {
            $shipment->port_to_door !== null => 'Port to Door',
            $shipment->port_to_port !== null => 'Port to Port',
            $shipment->door_to_port !== null => 'Door to Port',
            $shipment->pu_date !== null => 'Pickup',
            default => 'DO Received',
        };
    }

    private static function periodQuery(?int $month, ?int $year, ?int $day = null): Builder
    {
        return TsoShipment::query()
            ->when($day !== null, fn (Builder $query) => $query->whereDay('do_date', $day))
            ->when($month !== null, fn (Builder $query) => $query->whereMonth('do_date', $month))
            ->when($year !== null, fn (Builder $query) => $query->whereYear('do_date', $year));
    }

    private static function normalizedDestination(?string $destination): string
    {
        return strtoupper(trim((string) $destination));
    }
}
