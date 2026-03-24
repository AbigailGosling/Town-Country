<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $vehicles = [
            // Wolves (site_id = 1)
            ['reg' => 'DG15 AGO', 'vehicle_type_id' => 3, 'grossWeight' => '44T', 'site_id' => 1, 'max_pllts' => null],
            ['reg' => 'XR16 EYE', 'vehicle_type_id' => 3, 'grossWeight' => '23T', 'site_id' => 1, 'max_pllts' => null],
            ['reg' => 'GL67 BNB', 'vehicle_type_id' => 3, 'grossWeight' => '40T', 'site_id' => 1, 'max_pllts' => null],
            ['reg' => 'DX70 ULR', 'vehicle_type_id' => 3, 'grossWeight' => '44T', 'site_id' => 1, 'max_pllts' => null],
            ['reg' => 'E4 NDJ', 'vehicle_type_id' => 2, 'grossWeight' => '23T', 'site_id' => 1, 'max_pllts' => null],
            ['reg' => 'DG13 ODA', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'DG13 ODC', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'DG13 OCY', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'DG13 OCZ', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'DG14 WNU', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'DX66 KME', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'DX17 ZGP', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'WU19 HZV', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'WU19 HWB', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'WU19 HVS', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'WU18 FGF', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'WX20 ZVC', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 1, 'max_pllts' => 18],
            ['reg' => 'YB67 CJJ', 'vehicle_type_id' => 4, 'grossWeight' => '3.5T', 'payload' => '1.1T', 'site_id' => 1, 'max_pllts' => 3],
            ['reg' => 'HJ65 EMK', 'vehicle_type_id' => 4, 'grossWeight' => '3.5T', 'payload' => '1.1T', 'site_id' => 1, 'max_pllts' => 2],
            ['reg' => 'C418519', 'vehicle_type_id' => 1, 'grossWeight' => '44T*', 'payload' => '44T*', 'site_id' => 1, 'max_pllts' => 26],
            ['reg' => 'C375839', 'vehicle_type_id' => 1, 'grossWeight' => '44T*', 'payload' => '44T*', 'site_id' => 1, 'max_pllts' => 26],
            ['reg' => 'C464134', 'vehicle_type_id' => 1, 'grossWeight' => '44T*', 'payload' => '44T*', 'site_id' => 1, 'max_pllts' => 26],
            ['reg' => 'C545293', 'vehicle_type_id' => 1, 'grossWeight' => '44T*', 'payload' => '44T*', 'site_id' => 1, 'max_pllts' => 26],

            // Gatwick (site_id = 2)
            ['reg' => 'DG65 WVL', 'vehicle_type_id' => 2, 'grossWeight' => '18T', 'payload' => '9.8T', 'site_id' => 2, 'max_pllts' => 14],
            ['reg' => 'DG65 WUK', 'vehicle_type_id' => 2, 'grossWeight' => '18T', 'payload' => '9.8T', 'site_id' => 2, 'max_pllts' => 14],
            ['reg' => 'DG65 BKV', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 2, 'max_pllts' => 18],
            ['reg' => 'PN14 CBF', 'vehicle_type_id' => 2, 'grossWeight' => '18T', 'payload' => '9.8T', 'site_id' => 2, 'max_pllts' => 14],
            ['reg' => 'DG14 WNW', 'vehicle_type_id' => 2, 'grossWeight' => '26T', 'payload' => '14.5T', 'site_id' => 2, 'max_pllts' => 18],
            ['reg' => 'KP69 XPK', 'vehicle_type_id' => 4, 'grossWeight' => '3.5T', 'payload' => '1.1T', 'site_id' => 2, 'max_pllts' => 3],
            ['reg' => 'EU66 TEJ', 'vehicle_type_id' => 2, 'grossWeight' => '7.5T', 'payload' => '2.2T', 'site_id' => 2, 'max_pllts' => 4],

            // Taunton (site_id = 11)
            ['reg' => 'PN14 CAV', 'vehicle_type_id' => 2, 'grossWeight' => '18T', 'payload' => '9.8T', 'site_id' => 11, 'max_pllts' => 14],
            ['reg' => 'EU66 TEV', 'vehicle_type_id' => 2, 'grossWeight' => '7.5T', 'payload' => '2.2T', 'site_id' => 11, 'max_pllts' => 4],
            ['reg' => 'GD68 VNA', 'vehicle_type_id' => 4, 'grossWeight' => '3.5T', 'payload' => '1.1T', 'site_id' => 11, 'max_pllts' => 3],
        ];

        foreach ($vehicles as $vehicle) {
            $table = DB::connection('tandc_live')->table('vehicle');
            $maxPalletRows = is_numeric($vehicle['max_pllts'])
                ? (int) ceil(((int) $vehicle['max_pllts']) / 2)
                : null;

            $values = [
                'vehicle_type_id' => $vehicle['vehicle_type_id'],
                'grossWeight' => $vehicle['grossWeight'],
                'payload' => $vehicle['payload'],
                'site_id' => $vehicle['site_id'],
            ];

            if ($maxPalletRows !== null) {
                $values['max_pallet_rows'] = $maxPalletRows;
            }

            $existing = $table->where('reg', $vehicle['reg'])->first();

            if ($existing) {
                $table->where('id', $existing->id)->update($values);
                continue;
            }
            else
            {
                $insert = ['reg' => $vehicle['reg']] + $values;
                $table->insert($insert);
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
        // No-op: this migration performs data synchronization only.
    }
};
