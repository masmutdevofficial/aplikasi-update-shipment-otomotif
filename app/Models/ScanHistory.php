<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanHistory extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'no_rangka',
        'scan_date',
    ];

    protected function casts(): array
    {
        return [
            'scan_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
