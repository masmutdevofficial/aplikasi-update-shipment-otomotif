<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ShipmentExport;
use App\Exports\SpecialShipmentReportExport;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ReportService;
use App\Support\ReportDateFilter;
use App\Support\SpecialShipmentPerformance;
use App\Support\SpecialShipmentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $selectedReport = $this->validType($request->query('type'), $request->query('iso_type'));
        $selectedDate = $this->selectedDate($request, $selectedReport);
        $selectedMonth = $selectedDate === null ? $this->validMonth($request->query('month')) : null;
        $selectedYear = $selectedDate === null ? $this->validYear($request->query('year')) : null;
        $config = $selectedReport === 'dso' ? null : SpecialShipmentType::get($selectedReport);
        $dateField = ReportDateFilter::field($selectedReport, $request->query('date_field'));

        return view('admin.reports.index', [
            'selectedReport' => $selectedReport,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'selectedDateField' => $dateField,
            'hasSelectedDateField' => ReportDateFilter::selectedField($selectedReport, $request->query('date_field')) !== null,
            'selectedDate' => $selectedDate,
            'dateOptions' => ReportDateFilter::options($selectedReport),
            'availableYears' => ReportDateFilter::years($selectedReport, $dateField, $selectedYear),
            'reportConfig' => $config,
            'reportColumns' => $selectedReport === 'dso'
                ? ReportService::dsoColumns()
                : ReportService::specialColumns($config),
        ]);
    }

    public function data(Request $request)
    {
        $type = $this->validType($request->input('type'), $request->input('iso_type'));
        $selectedDate = $this->selectedDate($request, $type);
        $month = $this->validMonth($request->input('month'));
        $year = $this->validYear($request->input('year'));
        $config = $type === 'dso' ? null : SpecialShipmentType::get($type);
        $model = $config['model'] ?? Shipment::class;
        $dateField = ReportDateFilter::field($type, $request->input('date_field'));
        $reportColumns = $type === 'dso'
            ? ReportService::dsoColumns()
            : ReportService::specialColumns($config);
        $databaseColumns = collect($reportColumns)
            ->filter(fn (array $column) => $column['orderable'])
            ->pluck('data')
            ->all();
        $query = ReportDateFilter::apply($model::query(), $type, $dateField, $month, $year, $selectedDate);
        $recordsTotal = (clone $query)->count();
        $search = trim((string) $request->input('search.value', ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($databaseColumns, $search) {
                foreach ($databaseColumns as $column) {
                    $builder->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $recordsFiltered = (clone $query)->count();
        $orderIndex = (int) $request->input('order.0.column', 1);
        $defaultOrderColumn = $config['performance']['start'] ?? 'terima_do';
        $orderColumn = (string) $request->input("columns.{$orderIndex}.name", $defaultOrderColumn);
        $orderColumn = in_array($orderColumn, $databaseColumns, true) ? $orderColumn : $defaultOrderColumn;
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $start = max(0, (int) $request->input('start', 0));
        $length = min(100, max(10, (int) $request->input('length', 25)));
        $shipments = $query
            ->when($type === 'dso', fn (Builder $builder) => $builder->with(['shipmentUpdates', 'uploadedDocument']))
            ->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $type === 'dso'
            ? $shipments->map(function (Shipment $shipment, int $index) use ($start) {
                return [
                    'row_number' => $start + $index + 1,
                    ...collect(ReportService::flattenShipment($shipment))
                        ->map(fn (mixed $value) => $value ?? '-')
                        ->all(),
                ];
            })
            : $this->specialRows($shipments, $config, $type, $start);

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data->values(),
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $type = $this->validType($request->query('type'), $request->query('iso_type'));
        $selectedDate = $this->selectedDate($request, $type);
        $month = $this->validMonth($request->query('month'));
        $year = $this->validYear($request->query('year'));
        $dateField = ReportDateFilter::field($type, $request->query('date_field'));

        $filename = 'laporan_shipment_'.str_replace('-', '_', $type).'_'.now()->format('Ymd_His').'.xlsx';
        $export = $type === 'dso'
            ? new ShipmentExport(
                search: $request->input('search'),
                month: $month,
                year: $year,
                dateField: $dateField,
                selectedDate: $selectedDate,
            )
            : new SpecialShipmentReportExport($type, $month, $year, $dateField, $selectedDate);

        return Excel::download($export, $filename);
    }

    private function specialRows($shipments, array $config, string $type, int $start)
    {
        $documentUrls = ReportService::specialDocumentUrls($shipments, $config['identity']);

        return $shipments->map(function ($shipment, int $index) use ($config, $type, $start, $documentUrls) {
            $metrics = SpecialShipmentPerformance::calculate($type, $shipment);
            $row = ['row_number' => $start + $index + 1];

            foreach ($config['fields'] as $field => $fieldConfig) {
                $value = $field === 'sla_customer' ? $metrics['sla_customer'] : $shipment->{$field};
                $row[$field] = $value instanceof \DateTimeInterface
                    ? $value->format('d-M-y')
                    : ($value ?? '-');
            }

            foreach ($config['performance']['stages'] as $key => $_stage) {
                $row[$key] = $metrics[$key] ?? '-';
            }

            return [
                ...$row,
                'sla_actual' => $metrics['sla_actual'] ?? '-',
                'sla_result' => $metrics['sla_result'],
                'delay_days' => $metrics['delay_days'] ?? '-',
                'max_arrival' => $metrics['max_arrival']?->format('d-M-y') ?? '-',
                'progress' => $metrics['progress'],
                'document_url' => ReportService::specialDocumentUrl($documentUrls, $shipment->{$config['identity']}) ?? '-',
            ];
        });
    }

    private function selectedDate(Request $request, string $type): ?string
    {
        $validated = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);

        if (isset($validated['date'])) {
            $request->validate([
                'date_field' => ['required', 'string', Rule::in(array_keys(ReportDateFilter::options($type)))],
            ]);
        }

        return $validated['date'] ?? null;
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
}
