<?php

use App\Models\Permission;
use App\Models\SystemSetting;
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
        if (SystemSetting::find(0)) {
            $oldSS = SystemSetting::all()->reverse();
            foreach ($oldSS as $ss) {
                DB::select(DB::raw("UPDATE `tandc_live`.`system_settings` SET `id` = " . $ss->id+1 . " WHERE `id` = " . $ss->id));
            }
            DB::select(DB::raw("ALTER TABLE `tandc_live`.`system_settings` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT"));
        }
        $ss = new SystemSetting();
        $ss->key_name = "CREDIT_CHECK_FUNCTION_ENABLED";
        $ss->key_value = "1";
        $ss->save();

        $ss = new SystemSetting();
        $ss->key_name = "DELIVERY_DAY_FUNCTION_ENABLED";
        $ss->key_value = "1";
        $ss->save();

        $newPerm = new Permission();
        $newPerm->label = $newPerm->description = "Master Overrides";
        $newPerm->group = 3;
        $newPerm->name = $newPerm->file = "masteroverrides";
        $newPerm->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SystemSetting::where(['key_name'=>'CREDIT_CHECK_FUNCTION_ENABLED'])->first()->forceDelete();
        SystemSetting::where(['key_name'=>'DELIVERY_DAY_FUNCTION_ENABLED'])->first()->forceDelete();
        Permission::where(['name'=>"masteroverrides"])->first()->forceDelete();
    }
};
