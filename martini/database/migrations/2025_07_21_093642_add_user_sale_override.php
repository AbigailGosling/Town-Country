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
        Schema::connection('tandc_live')->table('users', function (Blueprint $table) {
            $table->boolean("override_saledate_check")->default(false);
        });
        Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
            $table->boolean("check_saledate")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try{
            Schema::connection('tandc_live')->table('users', function (Blueprint $table) {
                $table->dropColumn('override_saledate_check');
            });
        }
        catch (\Exception $e) {}
        try{
            Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
                $table->dropColumn('check_saledate');
            });
        }
        catch (\Exception $e) {}
    }
};
