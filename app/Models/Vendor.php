<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'vendor_name',
        'position',
        'created_by',
        'updated_by',
    ];

    /**
     * The user account linked to this vendor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The user who created this vendor record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The user who last updated this vendor record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function shipmentUpdates(): HasMany
    {
        return $this->hasMany(ShipmentUpdate::class);
    }

    /**
     * Available position values.
     */
    public static function positions(): array
    {
        return [
            'AT Storage Port',
            'ATD Kapal (Loading)',
            'ATA Kapal',
            'ATA Storage Port (Destination)',
            'AT PtD (Dooring)',
        ];
    }
}
