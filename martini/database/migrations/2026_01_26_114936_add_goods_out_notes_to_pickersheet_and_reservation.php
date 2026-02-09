<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->text('goods_out_notes')->nullable()->after('picksheet_note');
        });
        Schema::connection('tandc_live')->table('reservation', function (Blueprint $table) {
            $table->text('goods_out_notes')->nullable()->after('picksheet_note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('pickersheets', function (Blueprint $table) {
            $table->dropColumn('goods_out_notes');
        });
        Schema::connection('tandc_live')->table('reservation', function (Blueprint $table) {
            $table->dropColumn('goods_out_notes');
        });
    }
};
