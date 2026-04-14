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
        Schema::connection('tandc_live')->table('system_settings', function (Blueprint $table) {
            $table->boolean('hidden')->default(false)->after('key_value');
            $table->string('description')->nullable()->after('key_value');
            $table->string('var_type')->default("string")->after('key_value');
        });
        SystemSetting::whereIn('key_name', ["CREDIT_CHECK_FUNCTION_ENABLED","DELIVERY_DAY_FUNCTION_ENABLED"])
            ->update(['hidden' => true, 'var_type' => 'boolean']);
        SystemSetting::whereIn('key_name', ["OVERRIDER_START_DATE"])
            ->update(['hidden' => true]);
        $setting = new SystemSetting();
        $setting->key_name = "RRP_FUNCTION_ENABLED";
        $setting->key_value = "1";
        $setting->description = "Enable or disable force RRP.";
        $setting->var_type = "boolean";
        $setting->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('system_settings', function (Blueprint $table) {
            $table->dropColumn('hidden');
            $table->dropColumn('description');
            $table->dropColumn('var_type');
        });
    }
};
