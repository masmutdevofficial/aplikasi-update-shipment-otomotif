<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Production may contain legacy ENUM values stored as an empty string.
        // VARCHAR preserves those rows while allowing the new ISO/TSO positions;
        // application requests still validate against Vendor::positions().
        foreach (['vendors', 'shipment_updates', 'pending_vins'] as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `position` VARCHAR(100) NOT NULL");
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
