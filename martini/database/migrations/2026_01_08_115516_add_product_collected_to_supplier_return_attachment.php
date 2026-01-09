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
        Schema::connection('tandc_live')->table('supplier_return_attachment', function (Blueprint $table) {
            $table->boolean("product_collected")->default(false)->after("file_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('supplier_return_attachment', function (Blueprint $table) {
            $table->dropColumn("product_collected");
        });
    }
};
