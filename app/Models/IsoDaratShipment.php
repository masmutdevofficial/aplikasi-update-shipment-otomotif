<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IsoDaratShipment extends Model
{
    use HasUuids;

    protected $fillable = [
        'source_no',
        'no_so_booking',
        'no_quotation',
        'no_contract',
        'cargo_no_ka',
        'no_spb',
        'kategori_moda',
        'origin',
        'destination',
        'area',
        'terima_do',
        'keluar_dari_pdc',
        'at_ptd_dtd',
        'sla_customer',
    ];

    protected function casts(): array
    {
        return [
            'terima_do' => 'date',
            'keluar_dari_pdc' => 'date',
            'at_ptd_dtd' => 'date',
            'sla_customer' => 'integer',
        ];
    }
}
