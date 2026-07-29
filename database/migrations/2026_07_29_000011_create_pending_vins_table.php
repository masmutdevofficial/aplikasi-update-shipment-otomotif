<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_vins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_rangka', 17);
            $table->uuid('vendor_id');
            $table->enum('position', [
                'AT Storage Port',
                'ATD Kapal (Loading)',
                'ATA Kapal',
                'ATA Storage Port (Destination)',
                'AT PtD (Dooring)',
            ]);
            $table->date('scan_date');
            $table->string('document_path')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['no_rangka', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_vins');
    }
};
