<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_laut_shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('source_no')->nullable();
            $table->string('no_booking_dtp')->nullable();
            $table->string('no_booking_ptp')->nullable();
            $table->string('no_booking_ptd')->nullable();
            $table->string('no_quotation_dtp')->nullable();
            $table->string('no_quotation_ptp')->nullable();
            $table->string('no_quotation_ptd')->nullable();
            $table->string('no_contract_dtp')->nullable();
            $table->string('no_contract_ptp')->nullable();
            $table->string('no_contract_ptd')->nullable();
            $table->string('cargo')->nullable();
            $table->string('noka')->nullable();
            $table->string('kategori_moda')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->string('tujuan_pengiriman')->nullable();
            $table->date('terima_do')->nullable();
            $table->date('keluar_dari_pdc')->nullable();
            $table->string('jenis_kapal')->nullable();
            $table->date('at_storage_port')->nullable();
            $table->date('atd_kapal_loading')->nullable();
            $table->date('ata_kapal')->nullable();
            $table->date('ata_storage_port_destination')->nullable();
            $table->string('at_ptd_dtd')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_laut_shipments');
    }
};
