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
        DB::connection('tandc_live')->unprepared('SET SQL_SAFE_UPDATES = 0;');

        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->index('delivery_note_number');
            $table->index(['deleted','approved','container_id','id'],'deleted_approved_container_id');
        });

        Schema::connection('tandc_live')->table('pallet', function (Blueprint $table) {
            $table->index('is_hidden');
        });

        DB::connection('tandc_live')->unprepared("update cuts set species_id = null where species_id REGEXP '^[^0-9]+$' or species_id = '';");
        DB::connection('tandc_live')->unprepared("update cuts set cutgroup_id = null where cutgroup_id REGEXP '^[^0-9]+$' or cutgroup_id = '';");
        Schema::connection('tandc_live')->table('cuts', function (Blueprint $table) {
            $table->integer('species_id')->change();
            $table->integer('cutgroup_id')->change();
            $table->index('species_id');
            $table->index('cutgroup_id');
        });

        DB::connection('tandc_live')->unprepared("update product set nationality_id = -1 where nationality_id = '--';");
        DB::connection('tandc_live')->unprepared("update product set nationality_id = NULL where nationality_id = '';");
        Schema::connection('tandc_live')->table('product', function (Blueprint $table) {
            $table->integer('nationality_id')->change();
        });

        DB::connection('tandc_live')->unprepared('ALTER TABLE `pallet` ENGINE = InnoDB;');

        DB::connection('tandc_live')->unprepared('SET SQL_SAFE_UPDATES = 1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->dropIndex(['delivery_note_number']);
            $table->dropIndex('deleted_approved_container_id');
        });
        Schema::connection('tandc_live')->table('pallet', function (Blueprint $table) {
            $table->dropIndex(['is_hidden']);
        });
        Schema::connection('tandc_live')->table('cuts', function (Blueprint $table) {
            $table->text('species_id')->change();
            $table->text('cutgroup_id')->change();

            $table->dropIndex(['species_id']);
            $table->dropIndex(['cutgroup_id']);
        });
        Schema::connection('tandc_live')->table('product', function (Blueprint $table) {
            $table->string('nationality_id',5)->change();
        });
    }
};
