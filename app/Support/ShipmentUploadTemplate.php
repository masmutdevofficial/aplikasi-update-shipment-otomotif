<?php

namespace App\Support;

final class ShipmentUploadTemplate
{
    /**
     * @return array<string, string>
     */
    public static function dsoFields(): array
    {
        return [
            'lokasi' => 'Lokasi',
            'no_do' => 'No. DO',
            'type_kendaraan' => 'Type Kendaraan',
            'no_rangka' => 'No. Rangka',
            'no_engine' => 'No. Engine',
            'warna' => 'Warna',
            'asal_pdc' => 'Asal PDC',
            'kota' => 'Kota',
            'tujuan_pengiriman' => 'Tujuan Pengiriman',
            'terima_do' => 'Terima DO',
            'keluar_dari_pdc' => 'Keluar dari PDC',
            'nama_kapal' => 'Nama Kapal',
            'keberangkatan_kapal' => 'Keberangkatan Kapal',
            'at_storage_port' => 'AT Storage Port',
            'atd_kapal_loading' => 'ATD Kapal (Loading)',
            'ata_kapal' => 'ATA Kapal',
            'ata_storage_port_destination' => 'ATA Storage Port (Destination)',
            'at_ptd_dooring' => 'AT PtD (Dooring)',
        ];
    }

    /** @return array<int, string> */
    public static function dsoHeadings(): array
    {
        return array_values(self::dsoFields());
    }

    /** @return array<int, mixed> */
    public static function dsoSample(): array
    {
        return [
            'Jakarta Utara',
            'DO-DSO-001',
            'AVANZA 1.3 E M/T',
            'MHKM1BA3JFK123456',
            'K3VE1234567',
            'PUTIH',
            'PDC Sunter',
            'Surabaya',
            'Dealer ABC Surabaya',
            '2026-08-01',
            '2026-08-02',
            'KM NUSANTARA',
            '2026-08-03',
            '2026-08-02',
            '2026-08-03',
            '2026-08-05',
            '2026-08-05',
            '2026-08-06',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function specialImportableFields(array $config): array
    {
        return array_filter(
            $config['fields'],
            fn (array $fieldConfig) => ($fieldConfig['importable'] ?? true) !== false,
        );
    }

    /** @return array<int, string> */
    public static function specialHeadings(array $config): array
    {
        return array_column(self::specialImportableFields($config), 'label');
    }

    /** @return array<int, mixed> */
    public static function specialSample(string $type, array $config): array
    {
        $samples = [
            'tso' => [
                'unit_type' => 'AVANZA',
                'origin' => 'Karawang',
                'destination' => 'Makassar',
                'detail_destination' => 'Dealer ABC Makassar',
                'no_rangka' => 'MHKM1BA3JFK123457',
                'doc' => 'DO-TSO-001',
                'do_date' => '2026-08-01',
                'pu_date' => '2026-08-02',
                'door_to_port' => '2026-08-03',
                'port_to_port' => '2026-08-05',
                'port_to_door' => '2026-08-07',
                'vessel_ptp' => 'KM NUSANTARA',
                'sla_customer' => 8,
            ],
            'iso-darat' => [
                'source_no' => 1,
                'no_so_booking' => 'SO-ISO-D-001',
                'no_quotation' => 'QT-ISO-D-001',
                'no_contract' => 'CTR-ISO-D-001',
                'cargo_no_ka' => 'KA-001',
                'no_spb' => 'SPB-ISO-D-001',
                'kategori_moda' => 'DARAT',
                'origin' => 'Karawang',
                'destination' => 'Bandung',
                'area' => 'Jawa Barat',
                'terima_do' => '2026-08-01',
                'keluar_dari_pdc' => '2026-08-02',
                'at_ptd_dtd' => '2026-08-03',
                'sla_customer' => 3,
            ],
            'iso-laut' => [
                'source_no' => 1,
                'no_booking_dtp' => 'BK-DTP-001',
                'no_booking_ptp' => 'BK-PTP-001',
                'no_booking_ptd' => 'BK-PTD-001',
                'no_quotation_dtp' => 'QT-DTP-001',
                'no_quotation_ptp' => 'QT-PTP-001',
                'no_quotation_ptd' => 'QT-PTD-001',
                'no_contract_dtp' => 'CTR-DTP-001',
                'no_contract_ptp' => 'CTR-PTP-001',
                'no_contract_ptd' => 'CTR-PTD-001',
                'cargo' => 'UNIT',
                'noka' => 'MHKM1BA3JFK123458',
                'kategori_moda' => 'LAUT',
                'origin' => 'Karawang',
                'destination' => 'Makassar',
                'tujuan_pengiriman' => 'Dealer ABC Makassar',
                'terima_do' => '2026-08-01',
                'keluar_dari_pdc' => '2026-08-02',
                'jenis_kapal' => 'RORO',
                'at_storage_port' => '2026-08-02',
                'atd_kapal_loading' => '2026-08-03',
                'ata_kapal' => '2026-08-05',
                'ata_storage_port_destination' => '2026-08-05',
                'at_ptd_dtd' => '2026-08-06',
                'sla_customer' => 5,
            ],
        ];

        $sample = $samples[$type] ?? [];

        return array_map(
            fn (string $field) => $sample[$field] ?? null,
            array_keys(self::specialImportableFields($config)),
        );
    }

    public static function headerMatches(array $actual, array $expected): bool
    {
        $actual = array_map(
            fn (mixed $value) => trim((string) $value),
            array_values($actual),
        );

        while ($actual !== [] && end($actual) === '') {
            array_pop($actual);
        }

        return $actual === $expected;
    }

    public static function invalidHeaderMessage(string $label): string
    {
        return "Format file tidak sesuai Master Template {$label}. Download ulang master template, jangan mengubah, menghapus, menambah, atau memindahkan header kolom.";
    }
}
