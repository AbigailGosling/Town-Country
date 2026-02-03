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
        Schema::connection("tandc_live")->table('product', function (Blueprint $table) {
            $table->decimal("rrp1",8,3,true)->after("price")->nullable(true);
            $table->decimal("rrp2",8,3,true)->after("rrp1")->nullable(true);
            $table->decimal("rrp3",8,3,true)->after("rrp2")->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("tandc_live")->table('product', function (Blueprint $table) {
            $table->dropColumn("rrp1");
            $table->dropColumn("rrp2");
            $table->dropColumn("rrp3");
        });
    }
};
