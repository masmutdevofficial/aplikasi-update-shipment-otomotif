<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('lokasi', 50);
            $table->string('no_do', 50);
            $table->string('type_kendaraan', 50);
            $table->string('no_rangka', 17)->unique();
            $table->string('no_engine', 50);
            $table->string('warna', 50);
            $table->string('asal_pdc', 100);
            $table->string('kota', 100);
            $table->string('tujuan_pengiriman', 100);
            $table->date('terima_do');
            $table->date('keluar_dari_pdc');
            $table->string('nama_kapal', 100);
            $table->date('keberangkatan_kapal');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
