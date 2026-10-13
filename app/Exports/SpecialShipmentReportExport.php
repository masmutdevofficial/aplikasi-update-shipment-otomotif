<?php

namespace App\Exports;

use App\Services\ReportService;
use App\Support\ReportDateFilter;
use App\Support\SpecialShipmentPerformance;
use App\Support\SpecialShipmentType;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpecialShipmentReportExport implements FromArray, WithHeadings, WithStyles
{
    private array $config;

    public function __construct(
        private readonly string $type,
        private readonly ?int $month = null,
        private readonly ?int $year = null,
        private readonly ?string $dateField = null,
    ) {
        $this->config = SpecialShipmentType::get($type);
    }

    public function headings(): array
    {
        return [
            ...array_column($this->config['fields'], 'label'),
            ...array_column($this->config['performance']['stages'], 'label'),
            'SLA Actual',
            'Result',
            'Keterlambatan (Hari)',
            'Max Arrival',
            'Progress',
            'Dokumen',
        ];
    }

    public function array(): array
    {
        $model = $this->config['model'];
        $shipments = ReportDateFilter::apply($model::query(), $this->type, $this->dateField, $this->month, $this->year)
            ->latest()
            ->get();
        $documentUrls = ReportService::specialDocumentUrls($shipments, $this->config['identity']);

        return $shipments->map(function ($shipment) use ($documentUrls) {
            $metrics = SpecialShipmentPerformance::calculate($this->type, $shipment);
            $row = [];

            foreach ($this->config['fields'] as $field => $fieldConfig) {
                $value = $field === 'sla_customer'
                    ? $metrics['sla_customer']
                    : $shipment->{$field};
                $row[] = $fieldConfig['type'] === 'date'
                    ? ($value?->format('d-M-y') ?? '-')
                    : ($value ?? '-');
            }

            foreach ($this->config['performance']['stages'] as $key => $_stage) {
                $row[] = $metrics[$key] ?? '-';
            }

            return [
                ...$row,
                $metrics['sla_actual'] ?? '-',
                $metrics['sla_result'],
                $metrics['delay_days'] ?? '-',
                $metrics['max_arrival']?->format('d-M-y') ?? '-',
                $metrics['progress'],
                ReportService::specialDocumentUrl($documentUrls, $shipment->{$this->config['identity']}) ?? '-',
            ];
        })->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
