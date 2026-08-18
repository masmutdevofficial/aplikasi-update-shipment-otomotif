<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['tso_shipments', 'iso_darat_shipments', 'iso_laut_shipments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedSmallInteger('sla_customer')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['tso_shipments', 'iso_darat_shipments', 'iso_laut_shipments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('sla_customer');
            });
        }
    }
};
