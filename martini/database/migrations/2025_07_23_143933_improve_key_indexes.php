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
        DB::connection("tandc_live")->statement('ALTER TABLE `product` MODIFY `range_from`  VARCHAR(11);');
        DB::connection("tandc_live")->statement('ALTER TABLE `product` MODIFY `range_to`  VARCHAR(11);');
        DB::connection("tandc_live")->statement('ALTER TABLE `product` MODIFY `range_extension`  VARCHAR(11);');
        Schema::connection("tandc_live")->table('product', function(Blueprint $table)
        {
            $table->index(['range_from','range_to']);
        });
        Schema::connection("tandc_live")->table('pickeritems', function(Blueprint $table)
        {
            $table->index(['product_id','status','deleted']);
        });
        Schema::connection("tandc_live")->table('weights', function(Blueprint $table)
        {
            $table->index(['product_id','status_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
