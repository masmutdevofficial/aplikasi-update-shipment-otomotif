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
    public static function positionSummary(?int $month = null, ?int $year = null): array
    {
        return self::periodQuery($month, $year)
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

    private static function periodQuery(?int $month, ?int $year): Builder
    {
        return TsoShipment::query()
            ->when($month !== null, fn (Builder $query) => $query->whereMonth('do_date', $month))
            ->when($year !== null, fn (Builder $query) => $query->whereYear('do_date', $year));
    }

    private static function normalizedDestination(?string $destination): string
    {
        return strtoupper(trim((string) $destination));
    }
}
