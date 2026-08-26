<?php

namespace App\Support;

use App\Models\Shipment;
use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DsoSla
{
    private const SETTING_KEY = 'dso_sla_customers';

    /** @var array<int, array<string, int>> */
    private static array $customerOverrides = [];

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
     * Urutan target per kota: Belum Keluar PDC, Storage Port, Kapal Loading,
     * ATA Kapal, Storage Port Destination, dan PtD/Dooring.
     *
     * Dwelling aktual tidak berasal dari matriks ini. Nilainya dihitung
     * tersendiri untuk setiap shipment dari tanggal milestone shipment.
     *
     * @return array<string, array{stages: array<int, int>, total: int}>
     */
    public static function destinations(): array
    {
        $destinations = self::baseDestinations();

        foreach (self::customerOverrides() as $destination => $customer) {
            if (isset($destinations[$destination])) {
                $destinations[$destination]['total'] = $customer;
            }
        }

        return $destinations;
    }

    /** @return array<string, array{stages: array<int, int>, total: int}> */
    private static function baseDestinations(): array
    {
        return [
            'BALIKPAPAN' => ['stages' => [0, 3, 1, 3, 1, 0], 'total' => 8],
            'SAMARINDA' => ['stages' => [0, 3, 1, 3, 2, 0], 'total' => 9],
            'BANJARMASIN' => ['stages' => [0, 2, 1, 3, 1, 0], 'total' => 7],
            'MEDAN' => ['stages' => [0, 2, 1, 4, 1, 0], 'total' => 8],
            'MAKASSAR' => ['stages' => [0, 3, 1, 2, 2, 0], 'total' => 8],
            'PONTIANAK' => ['stages' => [0, 2, 1, 2, 1, 0], 'total' => 6],
            'GORONTALO' => ['stages' => [0, 2, 1, 4, 5, 0], 'total' => 12],
            'MANADO' => ['stages' => [0, 3, 1, 11, 3, 0], 'total' => 18],
        ];
    }

    /** @param array<string, int> $customers */
    public static function setCustomers(array $customers): void
    {
        $values = [];

        foreach (self::baseDestinations() as $destination => $_target) {
            $customer = filter_var($customers[$destination] ?? null, FILTER_VALIDATE_INT);

            if ($customer === false || $customer < 1 || $customer > 365) {
                throw new InvalidArgumentException("SLA Customer {$destination} tidak valid.");
            }

            $values[$destination] = $customer;
        }

        SystemSetting::query()->updateOrCreate(
            ['setting_key' => self::SETTING_KEY],
            ['setting_value' => json_encode($values, JSON_THROW_ON_ERROR)],
        );

        unset(self::$customerOverrides[self::applicationKey()]);
    }

    public static function targetFor(?string $kota, ?string $tujuan = null): ?array
    {
        $haystack = strtoupper(trim(($kota ?? '').' '.($tujuan ?? '')));

        foreach (self::destinations() as $destination => $target) {
            if (str_contains($haystack, $destination)) {
                return $target;
            }
        }

        return null;
    }

    /** @return array{completed: int, evaluated: int, late: int, percentage: float|int, otd: int, otd_percentage: float|int} */
    public static function delayStatistics(?int $month = null, ?int $year = null): array
    {
        $evaluatedShipments = self::periodQuery($month, $year)
            ->where('do_hold', false)
            ->whereNotNull('terima_do')
            ->get()
            ->filter(fn (Shipment $shipment) => $shipment->slaCustomer() !== null);
        $evaluated = $evaluatedShipments->count();
        $late = $evaluatedShipments
            ->filter(fn (Shipment $shipment) => $shipment->delayDays() > 0)
            ->count();
        $otd = $evaluated - $late;

        return [
            // Keep the old key for callers outside the dashboard.
            'completed' => $evaluated,
            'evaluated' => $evaluated,
            'late' => $late,
            'percentage' => $evaluated === 0 ? 0 : round($late / $evaluated * 100, 2),
            'otd' => $otd,
            'otd_percentage' => $evaluated === 0 ? 0 : round($otd / $evaluated * 100, 2),
        ];
    }

    /** @return array<int, array{city: string, total: int, otd: int, late: int, percentage: float|int}> */
    public static function lateByCity(?int $month = null, ?int $year = null): array
    {
        return self::periodQuery($month, $year)
            ->where('do_hold', false)
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
            ->where('do_hold', false)
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

    /**
     * Ringkasan dwelling aktual per kota pada periode dashboard.
     * Shipment yang belum mencapai milestone akhir dihitung sampai hari ini.
     *
     * @return array{
     *     origin: array<int, array{city: string, average: float|int, minimum: int, maximum: int}>,
     *     destination: array<int, array{city: string, average: float|int, minimum: int, maximum: int}>
     * }
     */
    public static function dwellingDetails(?int $month = null, ?int $year = null): array
    {
        $shipments = self::periodQuery($month, $year)->where('do_hold', false)->get();

        return [
            'origin' => self::dwellingByCity($shipments, fn (Shipment $shipment) => $shipment->dwellingOrigin()),
            'destination' => self::dwellingByCity($shipments, fn (Shipment $shipment) => $shipment->dwellingDestination()),
        ];
    }

    /**
     * @param  callable(Shipment): ?int  $daysResolver
     * @return array<int, array{city: string, average: float|int, minimum: int, maximum: int}>
     */
    private static function dwellingByCity(Collection $shipments, callable $daysResolver): array
    {
        return $shipments
            ->map(fn (Shipment $shipment) => [
                'city' => self::normalizedCity($shipment->kota),
                'days' => $daysResolver($shipment),
            ])
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

    /**
     * DO Performance berasal dari penjumlahan vertikal setiap posisi pada
     * Dashboard 2 — Posisi Barang per Kota. Setiap shipment hanya dihitung
     * sekali pada posisi terakhirnya dan shipment DO HOLD tetap terpisah.
     *
     * @return array<string, array{count: int, percentage: float|int}>
     */
    public static function doPerformanceStatistics(?int $month = null, ?int $year = null): array
    {
        $citySummaries = collect(self::positionSummary($month, $year));
        $total = (int) $citySummaries->sum('total');
        $positionCount = fn (string $position): int => (int) $citySummaries->sum(
            fn (array $summary) => $summary['positions'][$position]['count'] ?? 0
        );
        $counts = [
            'total_received' => $total,
            'not_departed_pdc' => $positionCount('Belum Keluar PDC'),
            'departed_pdc' => $positionCount('Keluar dari PDC'),
            'storage_port' => $positionCount('AT Storage Port'),
            'vessel_loading' => $positionCount('ATD Kapal (Loading)'),
            'vessel_arrived' => $positionCount('ATA Kapal'),
            'destination_storage' => $positionCount('ATA Storage Port (Destination)'),
            'ptd_dooring' => $positionCount('AT PtD (Dooring)'),
        ];

        return collect($counts)
            ->map(fn (int $count) => [
                'count' => $count,
                'percentage' => $total === 0 ? 0 : round($count / $total * 100, 2),
            ])
            ->all();
    }

    /** @return array{total: int, percentage: float|int} */
    public static function doHoldStatistics(?int $month = null, ?int $year = null): array
    {
        $shipments = self::periodQuery($month, $year)->get();
        $totalShipments = $shipments->count();
        $totalDoHold = $shipments->filter(fn (Shipment $shipment) => $shipment->isDoHold())->count();

        return [
            'total' => $totalDoHold,
            'percentage' => $totalShipments === 0 ? 0 : round($totalDoHold / $totalShipments * 100, 2),
        ];
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

    /** @return array<string, int> */
    private static function customerOverrides(): array
    {
        $applicationKey = self::applicationKey();

        if (isset(self::$customerOverrides[$applicationKey])) {
            return self::$customerOverrides[$applicationKey];
        }

        $stored = SystemSetting::query()
            ->where('setting_key', self::SETTING_KEY)
            ->value('setting_value');

        try {
            $decoded = is_string($stored) ? json_decode($stored, true, flags: JSON_THROW_ON_ERROR) : [];
        } catch (\JsonException) {
            $decoded = [];
        }

        $defaults = self::baseDestinations();
        $overrides = collect(is_array($decoded) ? $decoded : [])
            ->filter(fn (mixed $value, mixed $destination) => isset($defaults[$destination])
                && is_numeric($value)
                && (int) $value >= 1
                && (int) $value <= 365)
            ->map(fn (mixed $value) => (int) $value)
            ->all();

        return self::$customerOverrides[$applicationKey] = $overrides;
    }

    private static function applicationKey(): int
    {
        return function_exists('app') ? spl_object_id(app()) : 0;
    }
}
