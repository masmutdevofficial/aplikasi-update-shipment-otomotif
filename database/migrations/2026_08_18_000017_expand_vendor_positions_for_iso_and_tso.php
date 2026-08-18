<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<int, string> */
    private array $positions = [
        'AT Storage Port',
        'ATD Kapal (Loading)',
        'ATA Kapal',
        'ATA Storage Port (Destination)',
        'AT PtD (Dooring)',
        'AT PTD/DTD',
        'Door to Port (DTP)',
        'Port to Port (PTP)',
        'Port to Door (PTD)',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $enum = collect($this->positions)
            ->map(fn (string $position) => "'".str_replace("'", "''", $position)."'")
            ->implode(',');

        foreach (['vendors', 'shipment_updates', 'pending_vins'] as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `position` ENUM({$enum}) NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // VARCHAR preserves records that already use the new ISO/TSO positions.
        foreach (['vendors', 'shipment_updates', 'pending_vins'] as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `position` VARCHAR(100) NOT NULL");
        }
    }
};
