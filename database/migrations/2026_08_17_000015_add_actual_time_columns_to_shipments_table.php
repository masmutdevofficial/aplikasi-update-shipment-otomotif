<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->date('at_storage_port')->nullable()->after('keberangkatan_kapal');
            $table->date('atd_kapal_loading')->nullable()->after('at_storage_port');
            $table->date('ata_kapal')->nullable()->after('atd_kapal_loading');
            $table->date('ata_storage_port_destination')->nullable()->after('ata_kapal');
            $table->date('at_ptd_dooring')->nullable()->after('ata_storage_port_destination');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'at_storage_port',
                'atd_kapal_loading',
                'ata_kapal',
                'ata_storage_port_destination',
                'at_ptd_dooring',
            ]);
        });
    }
};
