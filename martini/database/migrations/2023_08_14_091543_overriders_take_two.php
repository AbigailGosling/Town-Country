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
        try{
            Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
                $table->dropColumn('markup_amount');        
            });
        }
        catch (\Exception $e) {}
        try{
            Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
                $table->dropColumn('markup_type');         
            });
        }
        catch (\Exception $e) {}
        Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
            $table->boolean('markup_enabled')->default(false);
            $table->decimal("markup_amount",8,3)->nullable();
        });
        $sysSet = SystemSetting::firstOrCreate([`key_name` => 'OVERRIDER_START_DATE']);
        if ($sysSet->key_value == null || $sysSet->key_value == "") $sysSet->key_value = '2023/01/01 00:00:00';
        $sysSet->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try{
            Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
                $table->dropColumn('markup_enabled');         
            });
        }
        catch (\Exception $e) {}
        try{
            Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
                $table->dropColumn('markup_amount');         
            });
        }
        catch (\Exception $e) {}
    }
};
