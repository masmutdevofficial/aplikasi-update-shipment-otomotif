<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('shipment_id');
            $table->uuid('vendor_id');
            $table->enum('position', [
                'AT Storage Port',
                'ATD Kapal (Loading)',
                'ATA Kapal',
                'ATA Storage Port (Destination)',
                'AT PtD (Dooring)',
            ]);
            $table->date('scan_date');
            $table->text('document_link')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('shipment_id')->references('id')->on('shipments')->cascadeOnDelete();
            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            // One update per shipment per position
            $table->unique(['shipment_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_updates');
    }
};
