<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('tandc_live')->table('vehicle_transport_pallet_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('committed_by_user_id')->nullable()->after('column');
            $table->string('committed_by_name', 255)->nullable()->after('committed_by_user_id');
            $table->timestamp('committed_at')->nullable()->after('committed_by_name');
        });
    }

    public function down()
    {
        Schema::connection('tandc_live')->table('vehicle_transport_pallet_allocations', function (Blueprint $table) {
            $table->dropColumn(['committed_by_user_id', 'committed_by_name', 'committed_at']);
        });
    }
};
