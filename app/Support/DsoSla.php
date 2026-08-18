<?php

namespace App\Support;

use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class DsoSla
{
    /**
     * SLA per tahapan sesuai matriks dari klien.
     * Urutan: Keluar PDC, Storage Port, Loading, ATA Kapal,
     * Storage Port Destination, dan PtD.
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

    /**
     * @return array{completed: int, late: int, percentage: float|int}
     */
    public static function delayStatistics(?int $month = null, ?int $year = null): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $destinationExpression = $isSqlite
            ? "UPPER(COALESCE(kota, '') || ' ' || COALESCE(tujuan_pengiriman, ''))"
            : "UPPER(CONCAT_WS(' ', COALESCE(kota, ''), COALESCE(tujuan_pengiriman, '')))";
        $dateDiffExpression = $isSqlite
            ? 'CAST(julianday(at_ptd_dooring) - julianday(terima_do) AS INTEGER)'
            : 'DATEDIFF(at_ptd_dooring, terima_do)';

        $completedQuery = Shipment::query()
            ->when($month !== null, fn ($query) => $query->whereMonth('terima_do', $month))
            ->when($year !== null, fn ($query) => $query->whereYear('terima_do', $year))
            ->whereNotNull('terima_do')
            ->whereNotNull('at_ptd_dooring')
            ->where(function ($query) use ($destinationExpression) {
                foreach (self::destinations() as $destination => $_target) {
                    $query->orWhereRaw("{$destinationExpression} LIKE ?", ["%{$destination}%"]);
                }
            });
        $completed = (clone $completedQuery)->count();
        $late = (clone $completedQuery)
            ->where(function ($query) use ($destinationExpression, $dateDiffExpression) {
                foreach (self::destinations() as $destination => $target) {
                    $query->orWhere(function ($destinationQuery) use ($destination, $target, $destinationExpression, $dateDiffExpression) {
                        $destinationQuery
                            ->whereRaw("{$destinationExpression} LIKE ?", ["%{$destination}%"])
                            ->whereRaw("{$dateDiffExpression} > ?", [$target['total']]);
                    });
                }
            })
            ->count();

        return [
            'completed' => $completed,
            'late' => $late,
            'percentage' => $completed === 0 ? 0 : round($late / $completed * 100, 2),
        ];
    }
}
