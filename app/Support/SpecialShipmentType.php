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
                    'sla_customer' => ['label' => 'SLA Customer (Days)', 'type' => 'integer', 'min' => 1],
                ],
                'performance' => [
                    'start' => 'do_date',
                    'final' => 'port_to_door',
                    'stages' => [
                        'lead_time_pickup' => ['label' => 'DO to Pickup', 'from' => 'do_date', 'to' => 'pu_date'],
                        'lead_time_door_to_port' => ['label' => 'Door to Port', 'from' => 'pu_date', 'to' => 'door_to_port'],
                        'lead_time_port_to_port' => ['label' => 'Port to Port', 'from' => 'door_to_port', 'to' => 'port_to_port'],
                        'lead_time_port_to_door' => ['label' => 'Port to Door', 'from' => 'port_to_port', 'to' => 'port_to_door'],
                    ],
                    'progress' => [
                        'port_to_door' => 'Port to Door',
                        'port_to_port' => 'Port to Port',
                        'door_to_port' => 'Door to Port',
                        'pu_date' => 'Pickup',
                        'do_date' => 'DO Received',
                    ],
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
                    'sla_customer' => ['label' => 'SLA Customer (Days)', 'type' => 'integer', 'min' => 1],
                ],
                'performance' => [
                    'start' => 'terima_do',
                    'final' => 'at_ptd_dtd',
                    'stages' => [
                        'lead_time_release' => ['label' => 'DO Release to Pickup', 'from' => 'terima_do', 'to' => 'keluar_dari_pdc'],
                        'lead_time_ptd_dtd' => ['label' => 'PTD/DTD', 'from' => 'keluar_dari_pdc', 'to' => 'at_ptd_dtd'],
                    ],
                    'progress' => [
                        'at_ptd_dtd' => 'PTD/DTD',
                        'keluar_dari_pdc' => 'Pickup',
                        'terima_do' => 'DO Received',
                    ],
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
                    'sla_customer' => ['label' => 'SLA Customer (Days)', 'type' => 'integer', 'min' => 1],
                ],
                'performance' => [
                    'start' => 'terima_do',
                    'final' => 'at_ptd_dtd',
                    'stages' => [
                        'lead_time_release' => ['label' => 'DO Release to Pickup', 'from' => 'terima_do', 'to' => 'keluar_dari_pdc'],
                        'lead_time_storage_port' => ['label' => 'Storage Port', 'from' => 'keluar_dari_pdc', 'to' => 'at_storage_port'],
                        'lead_time_loading' => ['label' => 'Dwelling Origin', 'from' => 'at_storage_port', 'to' => 'atd_kapal_loading', 'ongoing' => true],
                        'lead_time_aboard' => ['label' => 'Kapal (Aboard)', 'from' => 'atd_kapal_loading', 'to' => 'ata_kapal'],
                        'lead_time_storage_destination' => ['label' => 'Storage Port (Destination)', 'from' => 'ata_kapal', 'to' => 'ata_storage_port_destination'],
                        'lead_time_ptd_dtd' => ['label' => 'Dwelling Destination', 'from' => 'ata_storage_port_destination', 'to' => 'at_ptd_dtd', 'ongoing' => true],
                    ],
                    'progress' => [
                        'at_ptd_dtd' => 'PTD/DTD',
                        'ata_storage_port_destination' => 'Storage Port (Destination)',
                        'ata_kapal' => 'Kapal (Aboard)',
                        'atd_kapal_loading' => 'Kapal (Loading)',
                        'at_storage_port' => 'Storage Port',
                        'keluar_dari_pdc' => 'Pickup',
                        'terima_do' => 'DO Received',
                    ],
                ],
            ],
        ];
    }
}
