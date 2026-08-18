<?php

namespace App\Support;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\Builder;

class DsoSla
{
    /** @return array<int, string> */
    public static function positions(): array
    {
        return [
            'Belum Keluar PDC',
            'Keluar dari PDC',
            'AT Storage Port',
            'ATD Kapal (Loading)',
            'ATA Kapal',
            'ATA Storage Port (Destination)',
            'AT PtD (Dooring)',
        ];
    }

    /**
     * SLA per tahapan sesuai matriks dari klien.
     * Urutan: Keluar PDC, Storage Port, Dwelling Origin, ATA Kapal,
     * Storage Port Destination, dan Dwelling Destination.
     *
     * @return array<string, array{stages: array<int, int>, total: int}>
     */
    public static function destinations(): array
    {
        return [
            'BALIKPAPAN' => ['stages' => [2, 3, 1, 3, 0, 1], 'total' => 10],
            'SAMARINDA' => ['stages' => [2, 3, 1, 3, 0, 2], 'total' => 11],
            'BANJARMASIN' => ['stages' => [2, 2, 1, 3, 0, 1], 'total' => 9],
            'MEDAN' => ['stages' => [2, 2, 1, 4, 0, 1], 'total' => 10],
            'MAKASSAR' => ['stages' => [2, 3, 1, 2, 0, 2], 'total' => 10],
            'PONTIANAK' => ['stages' => [2, 2, 1, 2, 0, 1], 'total' => 8],
            'GORONTALO' => ['stages' => [2, 2, 1, 4, 0, 5], 'total' => 14],
            'MANADO' => ['stages' => [2, 3, 1, 11, 0, 3], 'total' => 20],
        ];
    }

    public static function targetFor(?string $kota, ?string $tujuan = null): ?array
    {
        $haystack = strtoupper(trim(($kota ?? '') . ' ' . ($tujuan ?? '')));

        foreach (self::destinations() as $destination => $target) {
            if (str_contains($haystack, $destination)) {
                return $target;
            }
        }

        return null;
    }

    /** @return array{completed: int, evaluated: int, late: int, percentage: float|int} */
    public static function delayStatistics(?int $month = null, ?int $year = null): array
    {
        $evaluatedShipments = self::periodQuery($month, $year)
            ->whereNotNull('terima_do')
            ->get()
            ->filter(fn (Shipment $shipment) => $shipment->slaCustomer() !== null);
        $evaluated = $evaluatedShipments->count();
        $late = $evaluatedShipments
            ->filter(fn (Shipment $shipment) => $shipment->delayDays() > 0)
            ->count();

        return [
            // Keep the old key for callers outside the dashboard.
            'completed' => $evaluated,
            'evaluated' => $evaluated,
            'late' => $late,
            'percentage' => $evaluated === 0 ? 0 : round($late / $evaluated * 100, 2),
        ];
    }

    /** @return array<int, array{city: string, total: int, otd: int, late: int, percentage: float|int}> */
    public static function lateByCity(?int $month = null, ?int $year = null): array
    {
        return self::periodQuery($month, $year)
            ->whereNotNull('terima_do')
            ->get()
            ->groupBy(fn (Shipment $shipment) => self::normalizedCity($shipment->kota))
            ->reject(fn ($shipments, string $city) => $city === '')
            ->map(function ($shipments, string $city) {
                $evaluated = $shipments
                    ->filter(fn (Shipment $shipment) => $shipment->slaCustomer() !== null);
                $total = $evaluated->count();
                $late = $evaluated
                    ->filter(fn (Shipment $shipment) => $shipment->delayDays() > 0)
                    ->count();

                return [
                    'city' => $city,
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

    /**
     * @return array<int, array{city: string, total: int, positions: array<string, array{count: int, percentage: float|int}>}>
     */
    public static function positionSummary(?int $month = null, ?int $year = null): array
    {
        return self::periodQuery($month, $year)
            ->whereNotNull('terima_do')
            ->get()
            ->groupBy(fn (Shipment $shipment) => self::normalizedCity($shipment->kota))
            ->reject(fn ($shipments, string $city) => $city === '')
            ->map(function ($shipments, string $city) {
                $total = $shipments->count();
                $counts = $shipments->countBy(fn (Shipment $shipment) => $shipment->currentPosition());
                $positions = [];

                foreach (self::positions() as $position) {
                    $count = (int) $counts->get($position, 0);
                    $positions[$position] = [
                        'count' => $count,
                        'percentage' => $total === 0 ? 0 : round($count / $total * 100, 2),
                    ];
                }

                return ['city' => $city, 'total' => $total, 'positions' => $positions];
            })
            ->sortBy('city')
            ->values()
            ->all();
    }

    private static function periodQuery(?int $month, ?int $year): Builder
    {
        return Shipment::query()
            ->when($month !== null, fn (Builder $query) => $query->whereMonth('terima_do', $month))
            ->when($year !== null, fn (Builder $query) => $query->whereYear('terima_do', $year));
    }

    private static function normalizedCity(?string $city): string
    {
        return strtoupper(trim((string) $city));
    }
}
