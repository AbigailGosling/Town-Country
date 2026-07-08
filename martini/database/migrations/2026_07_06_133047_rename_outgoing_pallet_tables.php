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
        // Schema::connection('tandc_live')->rename('outgoing_pallet', 'transport_pallets');

        Schema::connection('tandc_live')->table('transport_pallets', function (Blueprint $table) {
            $table->renameColumn('outgoing_pallet_type_id', 'transport_pallet_type_id');
        });

        // Schema::connection('tandc_live')->rename('outgoing_pallet_types', 'transport_pallet_types');

        Schema::connection('tandc_live')->table('transport_pallet_types', function (Blueprint $table) {
            $table->renameColumn('outgoing_pallet_type_id', 'transport_pallet_type_id');
        });

        // Schema::connection('tandc_live')->rename('outgoing_pallet_pickWeights', 'transport_pallet_pick_weights');

        Schema::connection('tandc_live')->table('transport_pallet_pick_weights', function (Blueprint $table) {
            $table->renameColumn('outgoing_pallet_id', 'transport_pallet_id');
        });

        // Schema::connection('tandc_live')->rename('vehicle_outgoing_pallet_allocations', 'vehicle_transport_pallet_allocations');

        Schema::connection('tandc_live')->table('vehicle_transport_pallet_allocations', function (Blueprint $table) {
            $table->renameColumn('outgoing_pallet_id', 'transport_pallet_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
