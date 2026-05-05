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
        Schema::connection('tandc_live')->table('reservation_product', function (Blueprint $table) {
            $table->index('reservation_id');
            $table->index('product_id');
            $table->index(['reservation_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('reservation_product', function (Blueprint $table) {
            $table->dropIndex(['reservation_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['reservation_id', 'product_id']);
        });
    }
};
