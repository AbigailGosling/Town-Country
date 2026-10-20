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
        Schema::connection('tandc_live')->table('transport_pallets', function (Blueprint $table) {
            $table->boolean('inbound')->default(false)->after('pod_sent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('transport_pallets', function (Blueprint $table) {
            $table->dropColumn('inbound');
        });
    }
};
