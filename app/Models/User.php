<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'phone',
        'password',
        'level',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Encrypt phone number before saving.
     */
    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt phone number when accessing.
     */
    public function getPhoneAttribute(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return $value;
        }
    }

    /**
     * Check if user is superadmin.
     */
    public function isSuperadmin(): bool
    {
        return $this->level === 'superadmin';
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->level === 'admin';
    }

    /**
     * Check if user is vendor.
     */
    public function isVendor(): bool
    {
        return $this->level === 'vendor';
    }

    /**
     * Get the user who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the vendor record associated with this user.
     */
    public function vendor(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Vendor::class);
    }
}
