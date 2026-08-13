<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_darat_shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('source_no')->nullable();
            $table->string('no_so_booking')->nullable();
            $table->string('no_quotation')->nullable();
            $table->string('no_contract')->nullable();
            $table->string('cargo_no_ka')->nullable();
            $table->string('no_spb')->nullable();
            $table->string('kategori_moda')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->string('area')->nullable();
            $table->date('terima_do')->nullable();
            $table->date('keluar_dari_pdc')->nullable();
            $table->date('at_ptd_dtd')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_darat_shipments');
    }
};
