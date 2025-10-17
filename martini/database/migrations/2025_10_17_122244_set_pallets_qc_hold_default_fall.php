<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`pallet` CHANGE COLUMN `qc_hold` `qc_hold` TINYINT(1) NOT NULL DEFAULT 0 ;");
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
