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
            $result[$key] = self::daysBetween(
                $from,
                self::dateValue($shipment->{$stage['to']}, $from?->year)
            );
        }

        $start = self::dateValue($shipment->{$performance['start']});
        $final = self::dateValue($shipment->{$performance['final']}, $start?->year);
        $actual = self::daysBetween($start, $final);
        $customer = $shipment->sla_customer !== null ? (int) $shipment->sla_customer : null;
        $status = $actual !== null && $customer !== null
            ? ($actual <= $customer ? 'OTD' : 'LATE')
            : 'IN PROGRESS';

        $result['sla_actual'] = $actual;
        $result['sla_result'] = $status;
        $result['delay_percentage'] = $actual !== null && $customer !== null && $customer > 0
            ? round(max(0, $actual - $customer) / $customer * 100, 2)
            : null;
        $result['max_arrival'] = $start !== null && $customer !== null
            ? $start->copy()->addDays($customer)
            : null;
        $result['progress'] = self::progress($shipment, $performance['progress'], $status);

        return $result;
    }

    /**
     * @return array{completed: int, late: int, percentage: float|int}
     */
    public static function statistics(string $type): array
    {
        $config = SpecialShipmentType::get($type);
        $model = $config['model'];
        $completed = 0;
        $late = 0;

        foreach ($model::query()->cursor() as $shipment) {
            $metrics = self::calculate($type, $shipment);

            if (!in_array($metrics['sla_result'], ['OTD', 'LATE'], true)) {
                continue;
            }

            $completed++;
            $late += $metrics['sla_result'] === 'LATE' ? 1 : 0;
        }

        return [
            'completed' => $completed,
            'late' => $late,
            'percentage' => $completed === 0 ? 0 : round($late / $completed * 100, 2),
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
