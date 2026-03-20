<?php

use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('tandc_live')->table('vehicle', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_pallet_rows')->default(5)->after('driver');
        });
        Vehicle::whereIn('reg',["DG15 AGO","DX70 ULR","GL67 BNB","XR16 EYE"])->update(['max_pallet_rows' => 9]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tandc_live')->table('vehicle', function (Blueprint $table) {
            $table->dropColumn('max_pallet_rows');
        });
    }
};
