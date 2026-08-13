<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpecialShipmentTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(
        private readonly array $config,
    ) {}

    public function headings(): array
    {
        return array_column($this->config['fields'], 'label');
    }

    public function array(): array
    {
        return [array_fill(0, count($this->config['fields']), null)];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
            ],
        ];
    }
}
