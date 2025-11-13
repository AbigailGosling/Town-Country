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
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`customers` CHANGE COLUMN `default_salesman_id` `default_salesman_id` INTEGER;");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`customers` ADD INDEX `default_salesman_id` (`default_salesman_id`);");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`customers` ADD INDEX `site_id` (`site_id`);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`customers` DROP INDEX `default_salesman_id`;");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`customers` DROP INDEX `site_id`;");
    }
};
