<?php

use App\Models\Site;
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
        Schema::connection('tandc_live')->table('site', function (Blueprint $table) {
            $table->decimal('lat', 12, 9)->nullable();
            $table->decimal('lon', 12, 9)->nullable()->after('lat');
        });
        $wol = Site::where("name","Wolverhampton")->first();
        if ($wol) {
            $wol->lat = 52.577817000;
            $wol->lon = -2.107758000;
            $wol->save();
        }
        $gat = Site::where("name","Gatwick")->first();
        if ($gat) {
            $gat->lat = 51.140880366;
            $gat->lon = -0.162313549;
            $gat->save();
        }
        $tau = Site::where("name","Taunton")->first();
        if ($tau) {
            $tau->lat = 51.025874332;
            $tau->lon = -3.131914573;
            $tau->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('site', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lon']);
        });
    }
};
