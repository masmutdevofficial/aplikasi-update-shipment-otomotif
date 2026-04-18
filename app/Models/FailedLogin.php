<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedLogin extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_address',
        'attempts',
        'last_attempt_at',
        'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'last_attempt_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }
}
