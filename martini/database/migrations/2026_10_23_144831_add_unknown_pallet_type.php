<?php

use App\Models\TransportPalletType;
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
       TransportPalletType::create([
           'name' => 'Unknown',
           'max_weight' => 9999.9999
       ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        TransportPalletType::where('name', 'Unknown')->delete();
    }
};
