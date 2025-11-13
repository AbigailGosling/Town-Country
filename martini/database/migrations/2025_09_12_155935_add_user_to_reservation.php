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
        Schema::connection('tandc_live')->create('reservation_product', function (Blueprint $table) {
            $table->id();
            $table->integer("reservation_id");
            $table->integer("product_id");
            $table->integer("target_count");
            $table->decimal("price",8,3,true);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->dropIfExists('reservation');
    }
};
