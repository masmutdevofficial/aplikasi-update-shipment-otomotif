<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScanHistory;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use App\Support\DashboardDateRange;
use App\Support\DashboardSlaAlert;
use App\Support\DsoSla;
use App\Support\IsoDashboard;
use App\Support\SpecialShipmentPerformance;
use App\Support\SpecialShipmentType;
use App\Support\TsoDashboard;
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
        [$startDate, $endDate] = DashboardDateRange::normalize(
            $request->query('start_date'),
            $request->query('end_date'),
            $request->query('day'),
            $request->query('month'),
            $request->query('year'),
        );
        $performanceType = $type === 'dso'
            ? null
            : ($type === 'tso' ? 'tso' : "iso-{$isoType}");
        $periodConfig = $performanceType
            ? SpecialShipmentType::get($performanceType)
            : ['model' => Shipment::class, 'performance' => ['start' => 'terima_do']];
        $dateField = $periodConfig['performance']['start'];
        $model = $periodConfig['model'];

        $shipmentQuery = $this->applyPeriod($model::query(), $dateField, $startDate, $endDate);
        $scanQuery = $this->applyPeriod(ScanHistory::query(), 'scan_date', $startDate, $endDate);
        $vendorQuery = $this->applyPeriod(Vendor::query(), 'created_at', $startDate, $endDate);
        $userQuery = $this->applyPeriod(User::query(), 'created_at', $startDate, $endDate);

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
            'selectedStartDate' => $startDate,
            'selectedEndDate' => $endDate,
            'dashboardSlaAlerts' => $this->slaAlerts($performanceType, $startDate, $endDate),
            'delayStats' => $type === 'dso' ? DsoSla::delayStatistics(startDate: $startDate, endDate: $endDate) : null,
            'dsoLateByCity' => $type === 'dso' ? DsoSla::lateByCity(startDate: $startDate, endDate: $endDate) : [],
            'dsoPositionSummary' => $type === 'dso' ? DsoSla::positionSummary(startDate: $startDate, endDate: $endDate) : [],
            'dsoDoPerformance' => $type === 'dso' ? DsoSla::doPerformanceStatistics(startDate: $startDate, endDate: $endDate) : null,
            'dsoDoHoldStats' => $type === 'dso' ? DsoSla::doHoldStatistics(startDate: $startDate, endDate: $endDate) : null,
            'dsoDwellingDetails' => $type === 'dso' ? DsoSla::dwellingDetails(startDate: $startDate, endDate: $endDate) : null,
            'dsoPositions' => DsoSla::positions(),
            'tsoPositionSummary' => $type === 'tso' ? TsoDashboard::positionSummary(startDate: $startDate, endDate: $endDate) : [],
            'tsoDoPerformance' => $type === 'tso' ? TsoDashboard::doPerformanceStatistics(startDate: $startDate, endDate: $endDate) : null,
            'tsoPositions' => TsoDashboard::positions(),
            'isoPositionSummary' => $type === 'iso'
                ? IsoDashboard::positionSummary($performanceType, startDate: $startDate, endDate: $endDate)
                : [],
            'isoPositions' => $type === 'iso' ? IsoDashboard::positions($performanceType) : [],
            'isoLateByCity' => $performanceType === 'iso-laut'
                ? IsoDashboard::lateByDestination(startDate: $startDate, endDate: $endDate)
                : [],
            'isoDoPerformance' => $performanceType === 'iso-laut'
                ? IsoDashboard::doPerformanceStatistics(startDate: $startDate, endDate: $endDate)
                : null,
            'isoDaratMilestones' => $performanceType === 'iso-darat'
                ? IsoDashboard::daratMilestoneStatistics(startDate: $startDate, endDate: $endDate)
                : null,
            'isoDwellingDetails' => $performanceType === 'iso-laut'
                ? IsoDashboard::dwellingDetails(startDate: $startDate, endDate: $endDate)
                : null,
            'specialDelayStats' => $performanceType
                ? SpecialShipmentPerformance::statistics($performanceType, startDate: $startDate, endDate: $endDate)
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
        [$startDate, $endDate] = DashboardDateRange::normalize(
            $request->query('start_date'),
            $request->query('end_date'),
            $request->query('day'),
            $request->query('month'),
            $request->query('year'),
        );
        $performanceType = $type === 'dso'
            ? null
            : ($type === 'tso' ? 'tso' : "iso-{$isoType}");

        return view('admin.dashboard.alerts', [
            'selectedDashboard' => $type,
            'selectedIsoType' => $isoType,
            'selectedStartDate' => $startDate,
            'selectedEndDate' => $endDate,
            'dashboardSlaAlerts' => $this->slaAlerts($performanceType, $startDate, $endDate),
        ]);
    }

    /** @return array{warning: array<int, string>, danger: array<int, string>, stages: array<string, array{warning: array<int, string>, danger: array<int, string>}>} */
    private function slaAlerts(?string $performanceType, ?string $startDate, ?string $endDate): array
    {
        return match ($performanceType) {
            'iso-darat' => DashboardSlaAlert::isoDarat(startDate: $startDate, endDate: $endDate),
            'iso-laut' => DashboardSlaAlert::isoLaut(startDate: $startDate, endDate: $endDate),
            null => DashboardSlaAlert::dso(startDate: $startDate, endDate: $endDate),
            default => ['warning' => [], 'danger' => [], 'stages' => []],
        };
    }

    private function applyPeriod(Builder $query, string $dateField, ?string $startDate, ?string $endDate): Builder
    {
        return DashboardDateRange::apply($query, $dateField, $startDate, $endDate);
    }
}
