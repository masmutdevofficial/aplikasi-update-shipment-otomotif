<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('documentable_type', 100);
            $table->uuid('documentable_id');
            $table->uuid('vendor_id');
            $table->string('identifier');
            $table->string('document_path');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['documentable_type', 'documentable_id']);
            $table->index('identifier');
            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_documents');
    }
};
