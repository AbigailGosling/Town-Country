<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('tandc_live')->create('vehicle_transport_pallet_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('outgoing_pallet_id');
            $table->integer('row')->nullable();
            $table->integer('column')->nullable();
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicle');
            $table->foreign('outgoing_pallet_id')->references('id')->on('outgoing_pallet');
            $table->unique(['vehicle_id', 'outgoing_pallet_id'], 'vehicle_pallet_unique');
        });
    }

    public function down()
    {
        Schema::connection('tandc_live')->dropIfExists('vehicle_transport_pallet_allocations');
    }
};
