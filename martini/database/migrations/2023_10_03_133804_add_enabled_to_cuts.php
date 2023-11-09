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
        Schema::connection('tandc_live')->table('cuts', function (Blueprint $table) {
            if (!Schema::connection('tandc_live')->hasColumn('cuts',"disabled"))$table->boolean("disabled")->default(false);
        });
        Schema::connection('tandc_live')->table('supplier', function (Blueprint $table) {
            if (!Schema::connection('tandc_live')->hasColumn('supplier',"disabled"))$table->boolean("disabled")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*Schema::connection('tandc_live')->table('cuts', function (Blueprint $table) {
            $table->dropColumn("enabled");
        });
        Schema::connection('tandc_live')->table('supplier', function (Blueprint $table) {
            $table->dropColumn("enabled");
        });*/
    }
};
