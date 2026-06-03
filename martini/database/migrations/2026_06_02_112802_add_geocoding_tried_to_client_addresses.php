<?php

use App\Models\ClientAddress;
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
            $table->boolean('geocoding_tried')->default(false);
        });
        $clientAddresses = ClientAddress::all();
        foreach ($clientAddresses as $clientAddress) {
            if ($clientAddress->lat && $clientAddress->lon) {
                $clientAddress->geocoding_tried = true;
                $clientAddress->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('client_addresses', function (Blueprint $table) {
            $table->dropColumn('geocoding_tried');
        });
    }
};
