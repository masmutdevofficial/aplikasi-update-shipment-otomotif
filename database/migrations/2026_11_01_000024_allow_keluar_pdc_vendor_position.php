<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep position storage consistent on both existing and fresh databases.
        // Valid positions are enforced by Vendor::positions() in form requests.
        foreach (['vendors', 'shipment_updates', 'pending_vins'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('position', 100)->change();
            });
        }
    }

    public function down(): void
    {
        // Retain the wider column to preserve any recorded PDC/ISO/TSO scans.
    }
};
