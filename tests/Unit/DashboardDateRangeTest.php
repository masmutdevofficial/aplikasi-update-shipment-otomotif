<?php

namespace Tests\Unit;

use App\Support\DashboardDateRange;
use PHPUnit\Framework\TestCase;

class DashboardDateRangeTest extends TestCase
{
    public function test_it_normalizes_a_valid_date_range(): void
    {
        $this->assertSame(
            ['2025-05-01', '2025-05-31'],
            DashboardDateRange::normalize('2025-05-01', '2025-05-31'),
        );
    }

    public function test_it_orders_reversed_date_ranges(): void
    {
        $this->assertSame(
            ['2025-05-01', '2025-05-31'],
            DashboardDateRange::normalize('2025-05-31', '2025-05-01'),
        );
    }

    public function test_it_keeps_legacy_month_filters_compatible(): void
    {
        $this->assertSame(
            ['2025-05-01', '2025-05-31'],
            DashboardDateRange::normalize(null, null, null, 5, 2025),
        );
    }

    public function test_it_rejects_invalid_dates(): void
    {
        $this->assertSame([null, null], DashboardDateRange::normalize('2025-02-30', 'invalid'));
    }
}
