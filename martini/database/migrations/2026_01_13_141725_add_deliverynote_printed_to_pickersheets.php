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
        Schema::connection('tandc_live')->table('pickersheets', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->unsignedInteger('deliverynote_printed_by')->nullable()->after("deliverynote_printed");
            $table->timestamp('deliverynote_printed_at')->nullable()->after("deliverynote_printed_by");

        });
        DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_pickersheets_trigger`;
        CREATE TRIGGER `insert_pickersheets_trigger` BEFORE INSERT ON `pickersheets`
         FOR EACH ROW SET NEW.`created_at` = NOW();
        ');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('pickersheets', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deliverynote_printed_at');
            $table->dropColumn('deliverynote_printed_by');
        });
        DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_pickersheets_trigger`;');
        DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `update_pickersheets_set_printed_trigger`;');
    }
};
