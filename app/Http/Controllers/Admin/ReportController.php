<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ShipmentExport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
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
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $shipments = $this->reportService->getReportForExport(
            search: null,
            dateFrom: $request->input('date_from'),
            dateTo: $request->input('date_to'),
        );

        return view('admin.reports.index', compact('shipments'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $filename = 'laporan_shipment_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new ShipmentExport(
                search: $request->input('search'),
                dateFrom: $request->input('date_from'),
                dateTo: $request->input('date_to'),
            ),
            $filename,
        );
    }
}
