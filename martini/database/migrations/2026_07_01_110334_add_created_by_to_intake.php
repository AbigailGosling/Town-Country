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
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->integer('created_by')->default(-1)->after('user_id');
        });
        DB::connection('tandc_live')->unprepared('ALTER TABLE `tandc_live`.`intake` CHANGE COLUMN `created_by` `created_by` INT NOT NULL ;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
