<?php

use App\Models\Vehicle;
use App\Models\VehicleType;
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
        $vt = new VehicleType();
        $vt->name = 'COLLECTION';
        $vt->save();

        foreach (Vehicle::where('reg', 'COLLECTION')->get() as $vehicle) {
            $vehicle->vehicle_type_id = $vt->id;
            $vehicle->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $vt = VehicleType::query()->where('name', 'COLLECTION')->first();
        foreach(Vehicle::where('vehicle_type_id', $vt->id)->get() as $vehicle) {
            $vehicle->vehicle_type_id = 1;
            $vehicle->save();

        }
        $vt->delete();
    }
};
