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
        DB::connection("tandc_live")->statement("SET SQL_SAFE_UPDATES = 0;");
        DB::connection("tandc_live")->statement("DELETE FROM tandc_live.pickersheet_documents WHERE pickersheet_id = '';");
        DB::connection("tandc_live")->statement("SET SQL_SAFE_UPDATES = 1;");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`pickersheet_documents` CHANGE COLUMN `user_id` `user_id` INT NULL DEFAULT NULL;");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`pickersheet_documents` CHANGE COLUMN `pickersheet_id` `pickersheet_id` INT NULL DEFAULT NULL;");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`pickersheet_documents` ADD INDEX `pickersheet_id` (`pickersheet_id`);");
        DB::connection("tandc_live")->statement("ALTER TABLE `tandc_live`.`pickersheet_documents` ADD INDEX `user_id` (`user_id`);");
        Schema::connection("tandc_live")->table('pickersheet_documents', function (Blueprint $table) {
            $table->boolean("pod");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*Schema::connection("tandc_live")->table('pickersheet_documents', function (Blueprint $table) {
            $table->dropColumn("pod");
            $table->text("pickersheet_id")->nullable(true)->change();
            $table->dropIndex('pickersheet_id');
            $table->text("user_id")->nullable(true)->change();
            $table->dropIndex('user_id');
        });*/
    }
};
