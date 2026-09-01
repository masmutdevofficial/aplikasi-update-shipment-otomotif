<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_vins', function (Blueprint $table) {
            $table->string('scan_photo_path')->nullable()->after('document_path');
        });

        Schema::table('shipment_updates', function (Blueprint $table) {
            $table->string('scan_photo_path')->nullable()->after('document_path');
        });
    }

    public function down(): void
    {
        Schema::table('shipment_updates', function (Blueprint $table) {
            $table->dropColumn('scan_photo_path');
        });

        Schema::table('pending_vins', function (Blueprint $table) {
            $table->dropColumn('scan_photo_path');
        });
    }
};
