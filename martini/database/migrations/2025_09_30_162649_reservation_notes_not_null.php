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
        DB::connection("tandc_live")->statement("ALTER TABLE `reservation` CHANGE COLUMN `picksheet_note` `picksheet_note` VARCHAR(191) NULL, CHANGE COLUMN `order_reference_number` `order_reference_number` VARCHAR(191) NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("tandc_live")->table("reservation",function (Blueprint $table) {
            $table->string("picksheet_note")->change();
            $table->string("order_reference_number")->change();
        });
    }
};
