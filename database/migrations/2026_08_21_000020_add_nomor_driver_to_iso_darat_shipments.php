<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iso_darat_shipments', function (Blueprint $table) {
            $table->string('nomor_driver', 50)->nullable()->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('iso_darat_shipments', function (Blueprint $table) {
            $table->dropColumn('nomor_driver');
        });
    }
};
