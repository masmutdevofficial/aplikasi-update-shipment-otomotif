<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'lokasi',
        'no_do',
        'type_kendaraan',
        'no_rangka',
        'no_engine',
        'warna',
        'asal_pdc',
        'kota',
        'tujuan_pengiriman',
        'terima_do',
        'keluar_dari_pdc',
        'nama_kapal',
        'keberangkatan_kapal',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'terima_do' => 'date',
            'keluar_dari_pdc' => 'date',
            'keberangkatan_kapal' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function shipmentUpdates(): HasMany
    {
        return $this->hasMany(ShipmentUpdate::class);
    }
}
