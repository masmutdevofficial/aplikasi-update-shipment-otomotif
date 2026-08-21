<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ShipmentExport;
use App\Exports\SpecialShipmentReportExport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\SpecialShipmentType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
    ) {}

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $selectedReport = $this->validType($request->query('type'), $request->query('iso_type'));
        $selectedMonth = $this->validMonth($request->query('month'));
        $selectedYear = $this->validYear($request->query('year'));
        $config = $selectedReport === 'dso' ? null : SpecialShipmentType::get($selectedReport);
        $model = $config['model'] ?? \App\Models\Shipment::class;
        $dateField = $config['performance']['start'] ?? 'terima_do';

        $shipments = $selectedReport === 'dso'
            ? $this->reportService->getReportForExport(
                search: $request->input('search'),
                month: $selectedMonth,
                year: $selectedYear,
            )
            : $model::query()
                ->when($selectedMonth !== null, fn (Builder $query) => $query->whereMonth($dateField, $selectedMonth))
                ->when($selectedYear !== null, fn (Builder $query) => $query->whereYear($dateField, $selectedYear))
                ->latest()
                ->get();

        return view('admin.reports.index', [
            'shipments' => $shipments,
            'selectedReport' => $selectedReport,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'availableYears' => $this->availableYears($model, $dateField, $selectedYear),
            'reportConfig' => $config,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $type = $this->validType($request->query('type'), $request->query('iso_type'));
        $month = $this->validMonth($request->query('month'));
        $year = $this->validYear($request->query('year'));

        $filename = 'laporan_shipment_'.str_replace('-', '_', $type).'_'.now()->format('Ymd_His').'.xlsx';
        $export = $type === 'dso'
            ? new ShipmentExport(
                search: $request->input('search'),
                month: $month,
                year: $year,
            )
            : new SpecialShipmentReportExport($type, $month, $year);

        return Excel::download($export, $filename);
    }

    private function validType(mixed $value, mixed $isoType = null): string
    {
        $type = strtolower((string) ($value ?: 'dso'));

        if ($type === 'iso') {
            return strtolower((string) $isoType) === 'laut' ? 'iso-laut' : 'iso-darat';
        }

        return in_array($type, ['dso', 'tso', 'iso-darat', 'iso-laut'], true) ? $type : 'dso';
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

    /** @return array<int, int> */
    private function availableYears(string $model, string $dateField, ?int $selectedYear): array
    {
        $minimum = $model::query()->whereNotNull($dateField)->min($dateField);
        $maximum = $model::query()->whereNotNull($dateField)->max($dateField);
        $currentYear = (int) now()->year;
        $minimumYear = $minimum ? Carbon::parse($minimum)->year : $currentYear;
        $maximumYear = $maximum ? Carbon::parse($maximum)->year : $currentYear;

        if ($selectedYear !== null) {
            $minimumYear = min($minimumYear, $selectedYear);
            $maximumYear = max($maximumYear, $selectedYear);
        }

        return range(max($currentYear, $maximumYear), min($minimumYear, $currentYear));
    }
}
