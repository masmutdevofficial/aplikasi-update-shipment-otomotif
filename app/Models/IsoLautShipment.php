<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class IsoLautShipment extends Model
{
    use HasUuids;

    protected $fillable = [
        'source_no',
        'no_booking_dtp',
        'no_booking_ptp',
        'no_booking_ptd',
        'no_quotation_dtp',
        'no_quotation_ptp',
        'no_quotation_ptd',
        'no_contract_dtp',
        'no_contract_ptp',
        'no_contract_ptd',
        'cargo',
        'noka',
        'kategori_moda',
        'origin',
        'destination',
        'tujuan_pengiriman',
        'terima_do',
        'keluar_dari_pdc',
        'jenis_kapal',
        'at_storage_port',
        'atd_kapal_loading',
        'ata_kapal',
        'ata_storage_port_destination',
        'at_ptd_dtd',
        'sla_customer',
    ];

    protected function casts(): array
    {
        return [
            'terima_do' => 'date',
            'keluar_dari_pdc' => 'date',
            'at_storage_port' => 'date',
            'atd_kapal_loading' => 'date',
            'ata_kapal' => 'date',
            'ata_storage_port_destination' => 'date',
            'sla_customer' => 'integer',
        ];
    }

    protected function atPtdDtd(): Attribute
    {
        return Attribute::get(function (mixed $value): mixed {
            if (!is_numeric($value) || (float) $value <= 1000) {
                return $value;
            }

            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return $value;
            }
        });
    }
}
