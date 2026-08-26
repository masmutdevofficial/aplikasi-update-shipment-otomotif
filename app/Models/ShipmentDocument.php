<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShipmentDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'vendor_id',
        'identifier',
        'document_path',
        'created_by',
        'updated_by',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
