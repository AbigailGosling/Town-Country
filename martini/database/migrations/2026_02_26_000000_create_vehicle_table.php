<?php

use App\Models\Site;
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
        // Enforce the database connection to 'tandc_live'
        Schema::connection('tandc_live')->create('vehicle_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45)->unique();
            $table->timestamps();
        });
        Schema::connection('tandc_live')->create('vehicle', function (Blueprint $table) {
            $table->id();
            $table->string('reg', 45)->nullable();
            $table->unsignedBigInteger('vehicle_type_id');
            $table->string('make', 45)->nullable();
            $table->string('model', 45)->nullable();
            $table->string('grossWeight', 45)->nullable();
            $table->string('payload', 45)->nullable();
            $table->unsignedInteger('site_id');
            $table->string('driver', 45)->nullable();

            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_type');
            $table->foreign('site_id')->references('id')->on('site');
        });
        // Seed vehicle_type table
        // Use Eloquent models for seeding
        $vehicleTypeModels = [];
        foreach ([
            'TRAILER',
            'RIGID',
            'TRACTOR UNIT',
            'VAN',
        ] as $type) {
            $vehicleTypeModels[$type] = VehicleType::create(['name' => $type]);
        }
        $siteId = 1;
        $vehicleData = [
            [
                'reg' => 'C464134',
                'vehicle_type_id' => $vehicleTypeModels['TRAILER']->id,
                'make' => 'LAWRENCE',
                'model' => null,
                'grossWeight' => '23T',
                'payload' => 'A1 8T, A2 8T, A3 8T  (MAX 23T)',
                'site_id' => $siteId,
                'driver' => 'N/A',
            ],
            [
                'reg' => 'DG13 ODA',
                'vehicle_type_id' => $vehicleTypeModels['RIGID']->id,
                'make' => 'Mercedes',
                'model' => 'Sprinter',
                'grossWeight' => '26T',
                'payload' => 'A1 9T, A2 11.5T, A3 7.5T     (18T MAX)',
                'site_id' => $siteId,
                'driver' => 'Barry',
            ],
            [
                'reg' => 'DG15 AGO',
                'vehicle_type_id' => $vehicleTypeModels['TRACTOR UNIT']->id,
                'make' => 'MAN',
                'model' => 'MN883',
                'grossWeight' => '44T',
                'payload' => '23T',
                'site_id' => $siteId,
                'driver' => 'Tracey',
            ],
            [
                'reg' => 'YB67 CJJ',
                'vehicle_type_id' => $vehicleTypeModels['VAN']->id,
                'make' => 'Mercedes',
                'model' => 'Sprinter',
                'grossWeight' => '3.5T',
                'payload' => '1.6T',
                'site_id' => $siteId,
                'driver' => 'Fred',
            ],
        ];
        foreach ($vehicleData as $vehicle) {
            Vehicle::create($vehicle);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->dropIfExists('vehicle');
        Schema::connection('tandc_live')->dropIfExists('vehicle_type');
    }
};
