<?php

use App\Models\SystemSetting;
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
        $tfa = new SystemSetting();
        $tfa->key_name = "TWO_FACTOR_ENABLED";
        $tfa->key_value = 1;
        $tfa->var_type = "boolean";
        $tfa->hidden = 0;
        $tfa->description = "Enable Two Factor Authentication";
        $tfa->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tfa = SystemSetting::where("key_name","TWO_FACTOR_ENABLED")->first();
        $tfa->forceDelete();
        $tfa->save();
    }
};
