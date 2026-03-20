<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tandc_live')->rename('palletsOut','pickWeightOut');
        Schema::connection('tandc_live')->create('outgoing_pallet', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->integer('address_id');
            $table->timestamps();
        });
        Schema::connection('tandc_live')->create('outgoing_pallet_pickWeights', function (Blueprint $table) {
            $table->id();
            $table->integer('outgoing_pallet_id');
            $table->integer('pickWeightOut_id');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->rename('pickWeightOut','palletOut');
        Schema::connection('tandc_live')->dropIfExists('outgoing_pallet');
        Schema::connection('tandc_live')->dropIfExists('outgoing_pallet_pickWeights');
    }
};
