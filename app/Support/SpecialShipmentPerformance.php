<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class SpecialShipmentPerformance
{
    /**
     * @return array<string, int|float|string|CarbonInterface|null>
     */
    public static function calculate(string $type, Model $shipment): array
    {
        $performance = SpecialShipmentType::get($type)['performance'];
        $result = [];

        foreach ($performance['stages'] as $key => $stage) {
            $from = self::dateValue($shipment->{$stage['from']});
            $to = self::dateValue($shipment->{$stage['to']}, $from?->year);
            $result[$key] = self::daysBetween(
                $from,
                $to ?? (($stage['ongoing'] ?? false) ? now()->startOfDay() : null)
            );
        }

        $start = self::dateValue($shipment->{$performance['start']});
        $final = self::dateValue($shipment->{$performance['final']}, $start?->year);
        $actual = self::daysBetween($start, $final ?? now()->startOfDay());
        $customer = self::slaCustomer($type, $shipment);
        $status = $actual !== null && $customer !== null
            ? ($actual <= $customer ? 'OTD' : 'LATE')
            : 'IN PROGRESS';

        $result['sla_actual'] = $actual;
        $result['sla_customer'] = $customer;
        $result['sla_result'] = $status;
        $result['delay_days'] = $actual !== null && $customer !== null
            ? max(0, $actual - $customer)
            : null;
        $result['max_arrival'] = $start !== null && $customer !== null
            ? $start->copy()->addDays($customer)
            : null;
        $result['progress'] = self::progress($shipment, $performance['progress'], $status);

        return $result;
    }

    public static function slaCustomer(string $type, Model $shipment): ?int
    {
        if (in_array($type, ['iso-darat', 'iso-laut'], true)) {
            $matrixCustomer = IsoSla::customerFor($type, $shipment->destination);

            if ($matrixCustomer !== null) {
                return $matrixCustomer;
            }
        }

        return $shipment->sla_customer !== null ? (int) $shipment->sla_customer : null;
    }

    /**
     * @return array{completed: int, evaluated: int, late: int, percentage: float|int}
     */
    public static function statistics(string $type, ?int $month = null, ?int $year = null): array
    {
        $config = SpecialShipmentType::get($type);
        $model = $config['model'];
        $dateField = $config['performance']['start'];
        $evaluated = 0;
        $late = 0;

        $query = $model::query()
            ->when($month !== null, fn ($builder) => $builder->whereMonth($dateField, $month))
            ->when($year !== null, fn ($builder) => $builder->whereYear($dateField, $year));

        foreach ($query->cursor() as $shipment) {
            $metrics = self::calculate($type, $shipment);

            if (!in_array($metrics['sla_result'], ['OTD', 'LATE'], true)) {
                continue;
            }

            $evaluated++;
            $late += $metrics['sla_result'] === 'LATE' ? 1 : 0;
        }

        return [
            // Keep the old key for callers outside the dashboard.
            'completed' => $evaluated,
            'evaluated' => $evaluated,
            'late' => $late,
            'percentage' => $evaluated === 0 ? 0 : round($late / $evaluated * 100, 2),
        ];
    }

    private static function progress(Model $shipment, array $milestones, string $status): string
    {
        foreach ($milestones as $field => $label) {
            if (self::dateValue($shipment->{$field}) !== null) {
                return $field === array_key_first($milestones) ? $status : $label;
            }
        }

        return 'Pending';
    }

    private static function daysBetween(?CarbonInterface $from, ?CarbonInterface $to): ?int
    {
        if ($from === null || $to === null) {
            return null;
        }

        return max(0, (int) $from->diffInDays($to, false));
    }

    private static function dateValue(mixed $value, ?int $fallbackYear = null): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if ($value === null || trim((string) $value) === '' || str_contains(strtoupper((string) $value), 'VALUE')) {
            return null;
        }

        $text = trim((string) $value);

        if ($fallbackYear !== null && preg_match('/^\d{1,2}[-\/]\p{L}{3}$/u', $text)) {
            $text .= "-{$fallbackYear}";
        }

        try {
            return Carbon::parse($text);
        } catch (\Throwable) {
            return null;
        }
    }
}
