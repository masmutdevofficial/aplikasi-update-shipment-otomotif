<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScanHistory;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use App\Support\DashboardSlaAlert;
use App\Support\DsoSla;
use App\Support\IsoDashboard;
use App\Support\SpecialShipmentPerformance;
use App\Support\SpecialShipmentType;
use App\Support\TsoDashboard;
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
        $day = $this->validDay($request->query('day'));
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

        $shipmentQuery = $this->applyPeriod($model::query(), $dateField, $day, $month, $year);
        $scanQuery = $this->applyPeriod(ScanHistory::query(), 'scan_date', $day, $month, $year);
        $vendorQuery = $this->applyPeriod(Vendor::query(), 'created_at', $day, $month, $year);
        $userQuery = $this->applyPeriod(User::query(), 'created_at', $day, $month, $year);

        if ($performanceType !== null) {
            $identityField = $periodConfig['identity'];
            $scanQuery->whereIn(
                'no_rangka',
                $model::query()->whereNotNull($identityField)->select($identityField)
            );
        }

        return view('admin.dashboard', [
            'selectedDashboard' => $type,
            'selectedIsoType' => $isoType,
            'selectedDay' => $day,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'availableYears' => $this->availableYears($model, $dateField, $year),
            'dashboardSlaAlerts' => $this->slaAlerts($performanceType, $month, $year, $day),
            'delayStats' => $type === 'dso' ? DsoSla::delayStatistics($month, $year, $day) : null,
            'dsoLateByCity' => $type === 'dso' ? DsoSla::lateByCity($month, $year, $day) : [],
            'dsoPositionSummary' => $type === 'dso' ? DsoSla::positionSummary($month, $year, $day) : [],
            'dsoDoPerformance' => $type === 'dso' ? DsoSla::doPerformanceStatistics($month, $year, $day) : null,
            'dsoDoHoldStats' => $type === 'dso' ? DsoSla::doHoldStatistics($month, $year, $day) : null,
            'dsoDwellingDetails' => $type === 'dso' ? DsoSla::dwellingDetails($month, $year, $day) : null,
            'dsoPositions' => DsoSla::positions(),
            'tsoPositionSummary' => $type === 'tso' ? TsoDashboard::positionSummary($month, $year, $day) : [],
            'tsoDoPerformance' => $type === 'tso' ? TsoDashboard::doPerformanceStatistics($month, $year, $day) : null,
            'tsoPositions' => TsoDashboard::positions(),
            'isoPositionSummary' => $type === 'iso'
                ? IsoDashboard::positionSummary($performanceType, $month, $year, $day)
                : [],
            'isoPositions' => $type === 'iso' ? IsoDashboard::positions($performanceType) : [],
            'isoLateByCity' => $performanceType === 'iso-laut'
                ? IsoDashboard::lateByDestination($month, $year, $day)
                : [],
            'isoDoPerformance' => $performanceType === 'iso-laut'
                ? IsoDashboard::doPerformanceStatistics($month, $year, $day)
                : null,
            'isoDaratMilestones' => $performanceType === 'iso-darat'
                ? IsoDashboard::daratMilestoneStatistics($month, $year, $day)
                : null,
            'isoDwellingDetails' => $performanceType === 'iso-laut'
                ? IsoDashboard::dwellingDetails($month, $year, $day)
                : null,
            'specialDelayStats' => $performanceType
                ? SpecialShipmentPerformance::statistics($performanceType, $month, $year, $day)
                : null,
            'dashboardShipmentTotal' => (clone $shipmentQuery)->count(),
            'dashboardScanTotal' => (clone $scanQuery)->count(),
            'dashboardVendorTotal' => (clone $vendorQuery)->count(),
            'dashboardUserTotal' => (clone $userQuery)->count(),
            'latestShipments' => (clone $shipmentQuery)->latest()->take(5)->get(),
            'latestScans' => (clone $scanQuery)->with('user')->latest('scan_date')->take(5)->get(),
        ]);
    }

    public function alerts(Request $request)
    {
        $type = strtolower((string) $request->query('type', 'dso'));
        $isoType = strtolower((string) $request->query('iso_type', 'darat'));
        $type = in_array($type, ['dso', 'tso', 'iso'], true) ? $type : 'dso';
        $isoType = in_array($isoType, ['darat', 'laut'], true) ? $isoType : 'darat';
        $day = $this->validDay($request->query('day'));
        $month = $this->validMonth($request->query('month'));
        $year = $this->validYear($request->query('year'));
        $performanceType = $type === 'dso'
            ? null
            : ($type === 'tso' ? 'tso' : "iso-{$isoType}");

        return view('admin.dashboard.alerts', [
            'selectedDashboard' => $type,
            'selectedIsoType' => $isoType,
            'selectedDay' => $day,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'dashboardSlaAlerts' => $this->slaAlerts($performanceType, $month, $year, $day),
        ]);
    }

    /** @return array{warning: array<int, string>, danger: array<int, string>, stages: array<string, array{warning: array<int, string>, danger: array<int, string>}>} */
    private function slaAlerts(?string $performanceType, ?int $month, ?int $year, ?int $day = null): array
    {
        return match ($performanceType) {
            'iso-darat' => DashboardSlaAlert::isoDarat($month, $year, $day),
            'iso-laut' => DashboardSlaAlert::isoLaut($month, $year, $day),
            null => DashboardSlaAlert::dso($month, $year, $day),
            default => ['warning' => [], 'danger' => [], 'stages' => []],
        };
    }

    private function applyPeriod(Builder $query, string $dateField, ?int $day, ?int $month, ?int $year): Builder
    {
        return $query
            ->when($day !== null, fn (Builder $builder) => $builder->whereDay($dateField, $day))
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

    private function validDay(mixed $value): ?int
    {
        $day = filter_var($value, FILTER_VALIDATE_INT);

        return $day !== false && $day >= 1 && $day <= 31 ? $day : null;
    }

    private function validYear(mixed $value): ?int
    {
        $year = filter_var($value, FILTER_VALIDATE_INT);

        return $year !== false && $year >= 2000 && $year <= 2100 ? $year : null;
    }
}
