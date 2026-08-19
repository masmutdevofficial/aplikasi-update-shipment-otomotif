<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScanHistory;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use App\Support\DsoSla;
use App\Support\SpecialShipmentPerformance;
use App\Support\SpecialShipmentType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $type = strtolower((string) $request->query('type', 'dso'));
        $isoType = strtolower((string) $request->query('iso_type', 'darat'));
        $type = in_array($type, ['dso', 'tso', 'iso'], true) ? $type : 'dso';
        $isoType = in_array($isoType, ['darat', 'laut'], true) ? $isoType : 'darat';
        $month = $this->validMonth($request->query('month'));
        $year = $this->validYear($request->query('year'));
        $performanceType = $type === 'dso'
            ? null
            : ($type === 'tso' ? 'tso' : "iso-{$isoType}");
        $periodConfig = $performanceType
            ? SpecialShipmentType::get($performanceType)
            : ['model' => Shipment::class, 'performance' => ['start' => 'terima_do']];
        $dateField = $periodConfig['performance']['start'];
        $model = $periodConfig['model'];

        $shipmentQuery = $this->applyPeriod($model::query(), $dateField, $month, $year);
        $scanQuery = $this->applyPeriod(ScanHistory::query(), 'scan_date', $month, $year);

        return view('admin.dashboard', [
            'selectedDashboard' => $type,
            'selectedIsoType' => $isoType,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'availableYears' => $this->availableYears($model, $dateField, $year),
            'delayStats' => $type === 'dso' ? DsoSla::delayStatistics($month, $year) : null,
            'dsoLateByCity' => $type === 'dso' ? DsoSla::lateByCity($month, $year) : [],
            'dsoPositionSummary' => $type === 'dso' ? DsoSla::positionSummary($month, $year) : [],
            'dsoDwellingDetails' => $type === 'dso' ? DsoSla::dwellingDetails($month, $year) : null,
            'dsoDoPerformance' => $type === 'dso' ? DsoSla::doPerformanceStatistics($month, $year) : null,
            'dsoPositions' => DsoSla::positions(),
            'specialDelayStats' => $performanceType
                ? SpecialShipmentPerformance::statistics($performanceType, $month, $year)
                : null,
            'dashboardShipmentTotal' => (clone $shipmentQuery)->count(),
            'dashboardScanTotal' => (clone $scanQuery)->count(),
            'dashboardVendorTotal' => Vendor::count(),
            'dashboardUserTotal' => User::count(),
            'latestShipments' => (clone $shipmentQuery)->latest()->take(5)->get(),
            'latestScans' => (clone $scanQuery)->with('user')->latest('scan_date')->take(5)->get(),
        ]);
    }

    private function applyPeriod(Builder $query, string $dateField, ?int $month, ?int $year): Builder
    {
        return $query
            ->when($month !== null, fn (Builder $builder) => $builder->whereMonth($dateField, $month))
            ->when($year !== null, fn (Builder $builder) => $builder->whereYear($dateField, $year));
    }

    /** @return array<int, int> */
    private function availableYears(string $model, string $dateField, ?int $selectedYear): array
    {
        $query = $model::query()->whereNotNull($dateField);
        $minimum = $query->min($dateField);
        $maximum = $query->max($dateField);
        $currentYear = (int) now()->year;
        $minimumYear = $minimum ? Carbon::parse($minimum)->year : $currentYear;
        $maximumYear = $maximum ? Carbon::parse($maximum)->year : $currentYear;

        if ($selectedYear !== null) {
            $minimumYear = min($minimumYear, $selectedYear);
            $maximumYear = max($maximumYear, $selectedYear);
        }

        return range(max($currentYear, $maximumYear), min($minimumYear, $currentYear));
    }

    private function validMonth(mixed $value): ?int
    {
        $month = filter_var($value, FILTER_VALIDATE_INT);

        return $month !== false && $month >= 1 && $month <= 12 ? $month : null;
    }

    private function validYear(mixed $value): ?int
    {
        $year = filter_var($value, FILTER_VALIDATE_INT);

        return $year !== false && $year >= 2000 && $year <= 2100 ? $year : null;
    }
}
