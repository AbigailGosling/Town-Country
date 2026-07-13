<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::connection('tandc_live')->unprepared('RENAME TABLE `outgoing_pallet` TO `transport_pallets`');
        DB::connection('tandc_live')->unprepared('RENAME TABLE `outgoing_pallet_types` TO `transport_pallet_types`');
        DB::connection('tandc_live')->unprepared('RENAME TABLE `outgoing_pallet_pickWeights` TO `transport_pallet_pick_weights`');
        DB::connection('tandc_live')->unprepared('RENAME TABLE `vehicle_outgoing_pallet_allocations` TO `vehicle_transport_pallet_allocations`');

        DB::connection('tandc_live')->unprepared('ALTER TABLE `transport_pallets` CHANGE `outgoing_pallet_type_id` `transport_pallet_type_id` BIGINT(20) UNSIGNED NOT NULL;');
        DB::connection('tandc_live')->unprepared('ALTER TABLE `transport_pallet_pick_weights` CHANGE `outgoing_pallet_id` `transport_pallet_id` BIGINT(20) UNSIGNED NOT NULL;');
        DB::connection('tandc_live')->unprepared('ALTER TABLE `vehicle_transport_pallet_allocations` CHANGE `outgoing_pallet_id` `transport_pallet_id` BIGINT(20) UNSIGNED NOT NULL;');

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
