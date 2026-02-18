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
        Schema::connection('tandc_live')->table('brands', function (Blueprint $table) {
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });
        DB::connection('tandc_live')->unprepared('ALTER TABLE `tandc_live`.`brands`
            CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
            CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ;');
        DB::connection('tandc_live')->unprepared("SET SQL_SAFE_UPDATES=0;");
        DB::connection('tandc_live')->unprepared("UPDATE `tandc_live`.`intakedocs` SET `intakeid` = -1 where `intakeid`  NOT REGEXP '^[0-9]+$';");
        DB::connection('tandc_live')->unprepared("ALTER TABLE `tandc_live`.`intakedocs` CHANGE COLUMN `intakeid` `intakeid` INT NULL DEFAULT NULL ,
            ADD INDEX `intakeid` (`intakeid` ASC) VISIBLE;");
        DB::connection('tandc_live')->unprepared("ALTER TABLE `tandc_live`.`intakedocs` ADD INDEX `intakeid_type_id` (`type_id` ASC, `intakeid` ASC) VISIBLE;");
        DB::connection('tandc_live')->unprepared("SET SQL_SAFE_UPDATES=1;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('brands', function (Blueprint $table) {
            $table->dropColumn('deleted');
            $table->dropTimestamps();
        });
        DB::connection('tandc_live')->unprepared("ALTER TABLE `tandc_live`.`intakedocs` DROP INDEX `intakeid_type_id`, DROP INDEX `intakeid`;");
    }
};
