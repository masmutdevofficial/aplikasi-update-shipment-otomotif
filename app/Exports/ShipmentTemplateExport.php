<?php

namespace App\Exports;

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
        return 'Format Upload';
    }

    public function headings(): array
    {
        return [
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
    }

    public function array(): array
    {
        // One example row so users understand the expected format
        return [
            [
                'Jakarta Utara',
                'DO-2026-00001',
                'AVANZA 1.3 E M/T',
                'MHKM1BA3JFK123456',
                'K3VE1234567',
                'PUTIH',
                'PDC Sunter',
                'Surabaya',
                'Dealer ABC Surabaya',
                '2026-04-01',
                '2026-04-03',
                'KM MERATUS BARITO',
                '2026-04-05',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Bold header row
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => 'center'],
            ],
            // Example row styling
            2 => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F0F9FF']],
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
        ];
    }
}
