<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('no_do', 50)->nullable()->change();
            $table->date('terima_do')->nullable()->change();
            $table->date('keluar_dari_pdc')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('no_do', 50)->nullable(false)->change();
            $table->date('terima_do')->nullable(false)->change();
            $table->date('keluar_dari_pdc')->nullable(false)->change();
        });
    }
};
