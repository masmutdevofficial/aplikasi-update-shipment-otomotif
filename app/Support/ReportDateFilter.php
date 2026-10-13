<?php

namespace App\Support;

use App\Models\Shipment;
use App\Services\ReportService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportDateFilter
{
    /** @var array<string, string> */
    private const SCAN_POSITIONS = [
        'at_storage_port' => 'AT Storage Port',
        'atd_kapal_loading' => 'ATD Kapal (Loading)',
        'ata_kapal' => 'ATA Kapal',
        'ata_storage_port_destination' => 'ATA Storage Port (Destination)',
        'at_ptd_dooring' => 'AT PtD (Dooring)',
    ];

    /** @return array<string, string> */
    public static function options(string $type): array
    {
        if ($type === 'dso') {
            $casts = (new Shipment)->getCasts();
            $options = collect(ReportService::dsoColumns())
                ->filter(fn (array $column) => ($casts[$column['data']] ?? null) === 'date')
                ->pluck('label', 'data')->all();
            $options['at_ptd_dooring'] = 'AT PtD (Dooring)';
        } else {
            $options = collect(SpecialShipmentType::get($type)['fields'])
                ->filter(fn (array $field) => ($field['input_type'] ?? $field['type']) === 'date')
                ->map(fn (array $field) => $field['label'])->all();
        }

        return [...$options, 'max_arrival' => 'Max Arrival'];
    }

    public static function field(string $type, mixed $value): string
    {
        if (is_string($value) && array_key_exists($value, self::options($type))) {
            return $value;
        }

        return $type === 'dso' ? 'terima_do' : SpecialShipmentType::get($type)['performance']['start'];
    }

    public static function apply(Builder $query, string $type, ?string $field, ?int $month, ?int $year): Builder
    {
        $field = self::field($type, $field);

        if ($month === null && $year === null) {
            return $query;
        }

        if ($type === 'dso' && isset(self::SCAN_POSITIONS[$field])) {
            $position = self::SCAN_POSITIONS[$field];

            // Use the vendor scan when present, just like the displayed report.
            return $query->where(function (Builder $builder) use ($position, $field, $month, $year) {
                $builder->whereHas('shipmentUpdates', function (Builder $updates) use ($position, $month, $year) {
                    self::period($updates->where('position', $position), 'scan_date', $month, $year);
                })->orWhere(function (Builder $master) use ($position, $field, $month, $year) {
                    $master->whereDoesntHave('shipmentUpdates', fn (Builder $updates) => $updates
                        ->where('position', $position)->whereNotNull('scan_date'));
                    self::period($master, $field, $month, $year);
                });
            });
        }

        if (self::requiresCalculation($type, $field)) {
            // Derived deadlines and historical text dates need the same parsing
            // as the report. Read in chunks before applying table pagination.
            $ids = [];
            foreach ((clone $query)->reorder()->lazyById(500) as $shipment) {
                $date = self::date($shipment, $type, $field);
                if ($date !== null && ($month === null || $date->month === $month)
                    && ($year === null || $date->year === $year)) {
                    $ids[] = $shipment->getKey();
                }
            }

            return $query->whereIn($query->getModel()->getQualifiedKeyName(), $ids);
        }

        return self::period($query, $field, $month, $year);
    }

    /** @return array<int, int> */
    public static function years(string $type, string $field, ?int $selectedYear): array
    {
        $field = self::field($type, $field);
        $model = $type === 'dso' ? Shipment::class : SpecialShipmentType::get($type)['model'];
        $query = $model::query();
        $years = [(int) now()->year];

        if (self::requiresCalculation($type, $field) || ($type === 'dso' && isset(self::SCAN_POSITIONS[$field]))) {
            if ($type === 'dso' && isset(self::SCAN_POSITIONS[$field])) {
                $query->with('shipmentUpdates');
            }
            foreach ($query->lazyById(500) as $shipment) {
                $date = self::date($shipment, $type, $field);
                if ($date !== null) {
                    $years[$date->year] = $date->year;
                }
            }
        } else {
            foreach ([$query->min($field), $query->max($field)] as $date) {
                if ($date !== null) {
                    $years[] = Carbon::parse($date)->year;
                }
            }
        }

        if ($selectedYear !== null) {
            $years[] = $selectedYear;
        }

        return range(max($years), min($years));
    }

    private static function period(Builder $query, string $field, ?int $month, ?int $year): Builder
    {
        return $query
            ->when($month !== null, fn (Builder $builder) => $builder->whereMonth($field, $month))
            ->when($year !== null, fn (Builder $builder) => $builder->whereYear($field, $year));
    }

    private static function requiresCalculation(string $type, string $field): bool
    {
        return $field === 'max_arrival' || ($type === 'iso-laut' && $field === 'at_ptd_dtd');
    }

    private static function date(Model $shipment, string $type, string $field): ?CarbonInterface
    {
        if ($field === 'max_arrival') {
            return $type === 'dso'
                ? $shipment->maxArrival()
                : SpecialShipmentPerformance::calculate($type, $shipment)['max_arrival'];
        }

        if ($type === 'dso' && isset(self::SCAN_POSITIONS[$field])) {
            return $shipment->shipmentUpdates->firstWhere('position', self::SCAN_POSITIONS[$field])?->scan_date
                ?? $shipment->{$field};
        }

        if ($type === 'iso-laut' && $field === 'at_ptd_dtd') {
            return SpecialShipmentPerformance::dateValue($shipment->{$field}, $shipment->terima_do?->year);
        }

        return $shipment->{$field};
    }
}
