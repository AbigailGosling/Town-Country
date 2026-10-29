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
        DB::connection('tandc_live')->unprepared("ALTER TABLE `tandc_live`.`invoice_payments` CHANGE COLUMN `meta_data` `meta_data` TEXT NULL DEFAULT NULL ;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tandc_live')->unprepared("ALTER TABLE `tandc_live`.`invoice_payments` CHANGE COLUMN `meta_data` `meta_data` VARCHAR(200) NULL DEFAULT NULL ;");
    }
};
