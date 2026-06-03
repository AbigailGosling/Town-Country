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
        Schema::connection('tandc_live')->table('client_addresses', function (Blueprint $table) {
            $table->decimal('lat', 11, 9)->nullable();
            $table->decimal('lng', 11, 9)->nullable()->after('lat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('client_addresses', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};
