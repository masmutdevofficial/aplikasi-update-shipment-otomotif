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

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PENDING = 'pending';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'username',
        'name',
        'email',
        'phone',
        'password',
        'level',
        'is_active',
        'status',
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

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('status')) {
                $user->is_active = $user->status === self::STATUS_ACTIVE;
            } elseif ($user->isDirty('is_active')) {
                $user->status = $user->is_active
                    ? self::STATUS_ACTIVE
                    : self::STATUS_INACTIVE;
            }
        });
    }

    /** @return array<int, string> */
    public static function statuses(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_PENDING, self::STATUS_INACTIVE];
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_INACTIVE => 'Nonaktif',
            default => 'Aktif',
        };
    }

    public function canLogin(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->is_active;
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
