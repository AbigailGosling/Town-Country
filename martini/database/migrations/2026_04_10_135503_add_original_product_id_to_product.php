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
        Schema::connection('tandc_live')->table('product', function (Blueprint $table) {
            $table->unsignedBigInteger('original_product_id')->nullable()->after('original_pallet_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('product', function (Blueprint $table) {
            $table->dropColumn('original_product_id');
        });
    }
};
