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
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->integer("container_id")->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->dropColumn("container_id");
        });
    }
};
