<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->boolean('do_hold')->default(false)->index()->after('at_ptd_dooring');
        });

        DB::table('shipments')
            ->whereRaw("UPPER(TRIM(nama_kapal)) = 'DO HOLD'")
            ->update(['do_hold' => true]);
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('do_hold');
        });
    }
};
