<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TsoShipment extends Model
{
    use HasUuids;

    protected $fillable = [
        'unit_type',
        'origin',
        'destination',
        'detail_destination',
        'no_rangka',
        'doc',
        'do_date',
        'pu_date',
        'door_to_port',
        'port_to_port',
        'port_to_door',
        'vessel_ptp',
        'sla_customer',
    ];

    protected function casts(): array
    {
        return [
            'do_date' => 'date',
            'pu_date' => 'date',
            'door_to_port' => 'date',
            'port_to_port' => 'date',
            'port_to_door' => 'date',
            'sla_customer' => 'integer',
        ];
    }
}
