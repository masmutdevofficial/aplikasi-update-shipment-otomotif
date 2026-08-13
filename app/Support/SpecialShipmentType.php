<?php

namespace App\Support;

use App\Models\IsoDaratShipment;
use App\Models\IsoLautShipment;
use App\Models\TsoShipment;

class SpecialShipmentType
{
    /**
     * @return array<string, mixed>
     */
    public static function get(string $type): array
    {
        $config = self::all()[$type] ?? null;

        abort_if($config === null, 404);

        return $config;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'tso' => [
                'label' => 'Shipment TSO',
                'short_label' => 'TSO',
                'model' => TsoShipment::class,
                'identity' => 'no_rangka',
                'fields' => [
                    'unit_type' => ['label' => 'Unit Type', 'type' => 'text'],
                    'origin' => ['label' => 'Origin', 'type' => 'text'],
                    'destination' => ['label' => 'Destination', 'type' => 'text'],
                    'detail_destination' => ['label' => 'Detail Destination', 'type' => 'text'],
                    'no_rangka' => ['label' => 'No Rangka', 'type' => 'text', 'max' => 17],
                    'doc' => ['label' => 'Doc', 'type' => 'text'],
                    'do_date' => ['label' => 'DO Date', 'type' => 'date'],
                    'pu_date' => ['label' => 'PU Date', 'type' => 'date'],
                    'door_to_port' => ['label' => 'Door to Port', 'type' => 'date'],
                    'port_to_port' => ['label' => 'Port to Port', 'type' => 'date'],
                    'port_to_door' => ['label' => 'Port to Door', 'type' => 'date'],
                    'vessel_ptp' => ['label' => 'Vessel PTP', 'type' => 'text'],
                ],
            ],
            'iso-darat' => [
                'label' => 'Shipment ISO Darat',
                'short_label' => 'ISO Darat',
                'model' => IsoDaratShipment::class,
                'identity' => 'no_spb',
                'fields' => [
                    'source_no' => ['label' => 'No', 'type' => 'integer'],
                    'no_so_booking' => ['label' => 'NO SO / BOOKING', 'type' => 'text'],
                    'no_quotation' => ['label' => 'NO Quotation', 'type' => 'text'],
                    'no_contract' => ['label' => 'NO Contract', 'type' => 'text'],
                    'cargo_no_ka' => ['label' => 'CARGO / NO KA', 'type' => 'text'],
                    'no_spb' => ['label' => 'NO SPB', 'type' => 'text'],
                    'kategori_moda' => ['label' => 'Kategori MODA', 'type' => 'text'],
                    'origin' => ['label' => 'Origin', 'type' => 'text'],
                    'destination' => ['label' => 'Destination', 'type' => 'text'],
                    'area' => ['label' => 'Area', 'type' => 'text'],
                    'terima_do' => ['label' => 'Terima DO', 'type' => 'date'],
                    'keluar_dari_pdc' => ['label' => 'Keluar dari PDC', 'type' => 'date'],
                    'at_ptd_dtd' => ['label' => 'AT PTD/DTD', 'type' => 'date'],
                ],
            ],
            'iso-laut' => [
                'label' => 'Shipment ISO Laut',
                'short_label' => 'ISO Laut',
                'model' => IsoLautShipment::class,
                'identity' => 'noka',
                'fields' => [
                    'source_no' => ['label' => 'No', 'type' => 'integer'],
                    'no_booking_dtp' => ['label' => 'NO BOOKING DTP', 'type' => 'text'],
                    'no_booking_ptp' => ['label' => 'NO BOOKING PTP', 'type' => 'text'],
                    'no_booking_ptd' => ['label' => 'NO BOOKING PTD', 'type' => 'text'],
                    'no_quotation_dtp' => ['label' => 'NO Quotation DTP', 'type' => 'text'],
                    'no_quotation_ptp' => ['label' => 'NO Quotation PTP', 'type' => 'text'],
                    'no_quotation_ptd' => ['label' => 'NO Quotation PTD', 'type' => 'text'],
                    'no_contract_dtp' => ['label' => 'NO Contract DTP', 'type' => 'text'],
                    'no_contract_ptp' => ['label' => 'NO Contract PTP', 'type' => 'text'],
                    'no_contract_ptd' => ['label' => 'NO Contract PTD', 'type' => 'text'],
                    'cargo' => ['label' => 'CARGO', 'type' => 'text'],
                    'noka' => ['label' => 'NOKA', 'type' => 'text'],
                    'kategori_moda' => ['label' => 'Kategori MODA', 'type' => 'text'],
                    'origin' => ['label' => 'Origin', 'type' => 'text'],
                    'destination' => ['label' => 'Destination', 'type' => 'text'],
                    'tujuan_pengiriman' => ['label' => 'Tujuan Pengiriman', 'type' => 'text'],
                    'terima_do' => ['label' => 'Terima DO', 'type' => 'date'],
                    'keluar_dari_pdc' => ['label' => 'Keluar dari PDC', 'type' => 'date'],
                    'jenis_kapal' => ['label' => 'Jenis Kapal', 'type' => 'text'],
                    'at_storage_port' => ['label' => 'AT Storage Port', 'type' => 'date'],
                    'atd_kapal_loading' => ['label' => 'ATD Kapal (Loading)', 'type' => 'date'],
                    'ata_kapal' => ['label' => 'ATA Kapal', 'type' => 'date'],
                    'ata_storage_port_destination' => ['label' => 'ATA Storage Port (Destination)', 'type' => 'date'],
                    'at_ptd_dtd' => ['label' => 'AT PTD/DTD', 'type' => 'text'],
                ],
            ],
        ];
    }
}
