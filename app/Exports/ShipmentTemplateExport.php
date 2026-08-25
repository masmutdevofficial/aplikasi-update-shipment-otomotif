<?php

namespace App\Exports;

use App\Support\ShipmentUploadTemplate;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShipmentTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Master Upload DSO';
    }

    public function headings(): array
    {
        return ShipmentUploadTemplate::dsoHeadings();
    }

    public function array(): array
    {
        return [ShipmentUploadTemplate::dsoSample()];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:R2');
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getStyle('A1:R2')->getAlignment()->setVertical('center');
        $sheet->getStyle('A1:R2')->getBorders()->getBottom()
            ->setBorderStyle('thin')
            ->getColor()->setRGB('CBD5E1');

        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => 'center', 'wrapText' => true],
            ],
            2 => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF7D6']],
                'font' => ['color' => ['rgb' => '5B6472']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Lokasi
            'B' => 20,  // No. DO
            'C' => 25,  // Type Kendaraan
            'D' => 22,  // No. Rangka
            'E' => 18,  // No. Engine
            'F' => 15,  // Warna
            'G' => 20,  // Asal PDC
            'H' => 18,  // Kota
            'I' => 25,  // Tujuan Pengiriman
            'J' => 18,  // Terima DO
            'K' => 20,  // Keluar dari PDC
            'L' => 25,  // Nama Kapal
            'M' => 25,  // Keberangkatan Kapal
            'N' => 20,  // AT Storage Port
            'O' => 22,  // ATD Kapal Loading
            'P' => 18,  // ATA Kapal
            'Q' => 32,  // ATA Storage Port Destination
            'R' => 20,  // AT PtD
        ];
    }
}
