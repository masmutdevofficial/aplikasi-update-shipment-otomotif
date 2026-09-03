<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DashboardDateRange
{
    /** @return array{0: ?string, 1: ?string} */
    public static function normalize(
        mixed $startDate,
        mixed $endDate,
        mixed $day = null,
        mixed $month = null,
        mixed $year = null,
    ): array {
        $startDate = self::validDate($startDate);
        $endDate = self::validDate($endDate);

        if ($startDate !== null && $endDate !== null && $startDate > $endDate) {
            return [$endDate, $startDate];
        }

        if ($startDate !== null || $endDate !== null) {
            return [$startDate, $endDate];
        }

        $day = filter_var($day, FILTER_VALIDATE_INT);
        $month = filter_var($month, FILTER_VALIDATE_INT);
        $year = filter_var($year, FILTER_VALIDATE_INT);

        if ($year === false || $year < 2000 || $year > 2100) {
            return [null, null];
        }

        if ($month !== false && $month >= 1 && $month <= 12) {
            $periodStart = Carbon::create($year, $month, 1)->startOfDay();

            if ($day !== false && $day >= 1 && $day <= $periodStart->daysInMonth) {
                $date = $periodStart->copy()->day($day)->format('Y-m-d');

                return [$date, $date];
            }

            return [$periodStart->format('Y-m-d'), $periodStart->endOfMonth()->format('Y-m-d')];
        }

        return [Carbon::create($year, 1, 1)->format('Y-m-d'), Carbon::create($year, 12, 31)->format('Y-m-d')];
    }

    public static function apply(Builder $query, string $dateField, ?string $startDate, ?string $endDate): Builder
    {
        return $query
            ->when($startDate !== null, fn (Builder $builder) => $builder->whereDate($dateField, '>=', $startDate))
            ->when($endDate !== null, fn (Builder $builder) => $builder->whereDate($dateField, '<=', $endDate));
    }

    private static function validDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', trim($value));
        } catch (\Throwable) {
            return null;
        }

        return $date !== false && $date->format('Y-m-d') === trim($value)
            ? $date->format('Y-m-d')
            : null;
    }
}
