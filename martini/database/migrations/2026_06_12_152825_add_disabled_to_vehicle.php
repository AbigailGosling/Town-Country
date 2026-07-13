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
        Schema::connection('tandc_live')->table('vehicle', function (Blueprint $table) {
            $table->boolean('disabled')->default(false);
        });
        Schema::connection('tandc_live')->table('client_addresses', function (Blueprint $table) {
            $table->boolean('collection')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('vehicle', function (Blueprint $table) {
            $table->dropColumn('disabled');
        });
        Schema::connection('tandc_live')->table('client_addresses', function (Blueprint $table) {
            $table->dropColumn('collection');
        });
    }
};
