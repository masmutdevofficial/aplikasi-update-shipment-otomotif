<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tso_shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('unit_type')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->string('detail_destination')->nullable();
            $table->string('no_rangka', 17)->nullable();
            $table->string('doc')->nullable();
            $table->date('do_date')->nullable();
            $table->date('pu_date')->nullable();
            $table->date('door_to_port')->nullable();
            $table->date('port_to_port')->nullable();
            $table->date('port_to_door')->nullable();
            $table->string('vessel_ptp')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tso_shipments');
    }
};
