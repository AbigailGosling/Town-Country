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
        Schema::connection('tandc_live')->table('customer_outstanding_cache', function (Blueprint $table) {
            $table->string("pickersheet_sha2",64)->nullable();
            $table->string("payment_sha2",64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('customer_outstanding_cache', function (Blueprint $table) {
            $table->dropColumn("pickersheet_sha2",64)->nullable();
            $table->dropColumn("payment_sha2",64)->nullable();
        });
    }
};
