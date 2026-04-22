<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('nama_kapal', 100)->nullable()->change();
            $table->date('keberangkatan_kapal')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('nama_kapal', 100)->nullable(false)->change();
            $table->date('keberangkatan_kapal')->nullable(false)->change();
        });
    }
};
