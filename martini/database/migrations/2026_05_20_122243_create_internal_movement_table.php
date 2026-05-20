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
        Schema::connection('tandc_live')->create('internal_pallet_movement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pallet_id');
            $table->unsignedBigInteger('from_location_id');
            $table->unsignedBigInteger('to_location_id');
            $table->unsignedBigInteger('movement_initiated_by');
            $table->unsignedBigInteger('movement_processed_by')->nullable();
            $table->boolean('site_to_site')->default(false);
            $table->boolean('processed')->default(false);
            $table->boolean('accepted')->default(false);
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
        Schema::connection('tandc_live')->dropIfExists('internal_pallet_movement');
    }
};
