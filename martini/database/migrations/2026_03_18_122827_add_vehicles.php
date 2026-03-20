<?php

use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Vehicle::all()->each->delete();
        $vehicles = [

            // WOLVES (site_id = 1)
            ['reg' => 'DG15 AGO', 'vehicle_type_id' => 3, 'grossWeight' => '44T', 'payload' => '23T', 'site_id' => 1],
            ['reg' => 'GL67 BNB', 'vehicle_type_id' => 3, 'grossWeight' => '40T', 'payload' => '20T', 'site_id' => 1],
            ['reg' => 'DX70 ULR', 'vehicle_type_id' => 3, 'grossWeight' => '44T', 'payload' => '23T', 'site_id' => 1],
            ['reg' => 'XR16 EYE', 'vehicle_type_id' => 3, 'grossWeight' => '44T', 'payload' => '23T', 'site_id' => 1],

            ['reg' => 'DG13 ODA', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => 'A1 9T, A2 11.5T, A3 7.5T (18T MAX)', 'site_id' => 1],
            ['reg' => 'DG13 ODC', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => 'A1 9T, A2 11.5T, A3 7.5T (18T MAX)', 'site_id' => 1],
            ['reg' => 'DG13 OCY', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => 'A1 9T, A2 11.5T, A3 7.5T (18T MAX)', 'site_id' => 1],
            ['reg' => 'DG13 OCZ', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => 'A1 9T, A2 11.5T, A3 7.5T (18T MAX)', 'site_id' => 1],

            ['reg' => 'YB67 CJJ', 'vehicle_type_id' => 4, 'grossWeight' => '3.5T', 'payload' => '1.6T', 'site_id' => 1],
            ['reg' => 'HJ65 EMK', 'vehicle_type_id' => 4, 'grossWeight' => '3.5T', 'payload' => '1.8T', 'site_id' => 1],

            ['reg' => 'C464134', 'vehicle_type_id' => 1, 'grossWeight' => '23T', 'payload' => 'A1 8T, A2 8T, A3 8T (MAX 23T)', 'site_id' => 1],

            // GATWICK (site_id = 2)
            ['reg' => 'DG65 WVL', 'vehicle_type_id' => 2, 'grossWeight' => '18T', 'payload' => 'A1 7.5T, A2 11.5T (MAX 12T)', 'site_id' => 2],
            ['reg' => 'DG65 WUK', 'vehicle_type_id' => 2, 'grossWeight' => '18T', 'payload' => 'A1 7.5T, A2 11.5T (MAX 12T)', 'site_id' => 2],
            ['reg' => 'DG65 BKV', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => 'A1 8T, A2 11.5T, A3 7.5T (18T MAX)', 'site_id' => 2],

            ['reg' => 'EU66 TEJ', 'vehicle_type_id' => 2, 'grossWeight' => '7.5T', 'payload' => 'A1 3.6T, A2 5T (MAX 3.6T)', 'site_id' => 2],

            // TAUNTON (site_id = 11)
            ['reg' => 'PN14 CAV', 'vehicle_type_id' => 2, 'grossWeight' => '18T', 'payload' => 'A1 7.5T, A2 11.5T (MAX 12T)', 'site_id' => 11],
            ['reg' => 'EU66 TEV', 'vehicle_type_id' => 2, 'grossWeight' => '7.5T', 'payload' => 'A1 3.6T, A2 5T (MAX 3.6T)', 'site_id' => 11],
            ['reg' => 'GD68 VNA', 'vehicle_type_id' => 4, 'grossWeight' => '3.5T', 'payload' => '2T', 'site_id' => 11],
        ];

        foreach ($vehicles as $vehicle) {
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
        //
    }
};
