<?php

namespace App\Exports;

use App\Models\Vendor;
use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShipmentExport implements FromArray, WithHeadings, WithStyles
{
    public function __construct(
        protected ?string $search = null,
        protected ?int $month = null,
        protected ?int $year = null,
    ) {}

    public function headings(): array
    {
        $headings = [
            'Lokasi',
            'No. DO',
            'Type Kendaraan',
            'No. Rangka',
            'No. Engine',
            'Warna',
            'Asal PDC',
            'Kota',
            'Tujuan Pengiriman',
            'Terima DO',
            'Keluar dari PDC',
            'Nama Kapal',
            'Keberangkatan Kapal',
        ];

        // Add vendor position columns
        foreach (Vendor::positions() as $position) {
            $headings[] = $position;
        }

        $headings[] = 'Dokumen Scan';

        return $headings;
    }

    public function array(): array
    {
        $reportService = app(ReportService::class);

        $shipments = $reportService->getReportForExport(
            search: $this->search,
            month: $this->month,
            year: $this->year,
        );

        $rows = [];

        foreach ($shipments as $shipment) {
            $flat = ReportService::flattenShipment($shipment);
            $rows[] = array_values($flat);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
