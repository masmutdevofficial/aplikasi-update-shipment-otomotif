<?php

namespace App\Exports;

use App\Support\ShipmentUploadTemplate;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpecialShipmentTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly string $type,
        private readonly array $config,
    ) {}

    public function title(): string
    {
        return 'Master Upload ' . $this->config['short_label'];
    }

    public function headings(): array
    {
        return ShipmentUploadTemplate::specialHeadings($this->config);
    }

    public function array(): array
    {
        return [ShipmentUploadTemplate::specialSample($this->type, $this->config)];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}2");
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getStyle("A1:{$lastColumn}2")->getAlignment()->setVertical('center');
        $sheet->getStyle("A1:{$lastColumn}2")->getBorders()->getBottom()
            ->setBorderStyle('thin')
            ->getColor()->setRGB('CBD5E1');

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => 'center', 'wrapText' => true],
            ],
            2 => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF7D6']],
                'font' => ['color' => ['rgb' => '5B6472']],
            ],
        ];
    }
}
