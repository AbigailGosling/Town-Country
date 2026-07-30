<?php

use Illuminate\Database\Migrations\Migration;
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
        DB::connection("tandc_live")->unprepared("ALTER TABLE `tandc_live`.`pickernotifications` CHANGE COLUMN `message` `message` TINYTEXT NOT NULL ;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection("tandc_live")->transaction(function(){
            DB::connection("tandc_live")->unprepared("SET SQL_SAFE_UPDATES = 0;");
            DB::connection("tandc_live")->unprepared("UPDATE `tandc_live`.`pickernotifications` SET `message` = LEFT(`message`,191);");
            DB::connection("tandc_live")->unprepared("ALTER TABLE `tandc_live`.`pickernotifications` CHANGE COLUMN `message` `message` VARCHAR(191) NOT NULL ;");
            DB::connection("tandc_live")->unprepared("SET SQL_SAFE_UPDATES = 1;");
        });

    }
};
