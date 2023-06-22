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
        Schema::connection('tandc_live')->table('pickerSheets', function (Blueprint $table) {
            $table->string("transaction_id",50)->nullable();
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
            Schema::connection('tandc_live')->table('pickerSheets', function (Blueprint $table) {
                $table->dropColumn('transaction_id');
            });
        }
        catch (\Exception $e) {}
    }
};
