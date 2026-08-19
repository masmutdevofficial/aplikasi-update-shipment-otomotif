<?php

namespace App\Support;

use App\Models\SystemSetting;
use InvalidArgumentException;

class VendorAccess
{
    public const MODE_ACTIVE = 'active';

    public const MODE_MAINTENANCE = 'maintenance';

    public const SETTING_KEY = 'vendor_access_mode';

    public static function mode(): string
    {
        $mode = SystemSetting::query()
            ->where('setting_key', self::SETTING_KEY)
            ->value('setting_value');

        return in_array($mode, self::modes(), true)
            ? $mode
            : self::MODE_ACTIVE;
    }

    public static function isMaintenance(): bool
    {
        return self::mode() === self::MODE_MAINTENANCE;
    }

    public static function setMode(string $mode): void
    {
        if (! in_array($mode, self::modes(), true)) {
            throw new InvalidArgumentException('Mode akses vendor tidak valid.');
        }

        SystemSetting::query()->updateOrCreate(
            ['setting_key' => self::SETTING_KEY],
            ['setting_value' => $mode],
        );
    }

    /** @return array<int, string> */
    public static function modes(): array
    {
        return [self::MODE_ACTIVE, self::MODE_MAINTENANCE];
    }

    public static function maintenanceMessage(): string
    {
        return 'Portal vendor sedang dalam mode Maintenance. Login dan akses vendor sementara dinonaktifkan. Silakan hubungi administrator.';
    }
}
