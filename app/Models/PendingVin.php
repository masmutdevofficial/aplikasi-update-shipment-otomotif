<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingVin extends Model
{
    use HasUuids;

    protected $fillable = [
        'no_rangka',
        'vendor_id',
        'position',
        'scan_date',
        'document_path',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return ['scan_date' => 'date'];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
