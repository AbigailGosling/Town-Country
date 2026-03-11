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
        Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
            $table->boolean('cost_check_enabled')->default(true)->after('override_cost_check');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
            $table->dropColumn('cost_check_enabled');
        });
    }
};
