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
        Schema::connection('tandc_live')->table('outgoing_pallet', function (Blueprint $table) {
            $table->boolean('pod_sent')->default(false)->after('dispatched');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('outgoing_pallet', function (Blueprint $table) {
            $table->dropColumn('pod_sent');
        });
    }
};
