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
            $table->boolean("display_on_arrivals")->default(true);
        });
        foreach(Site::whereNotIn("id",[1,2,11])->get() as $site)
        {
            $site->display_on_arrivals = false;
            $site->save();
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
            $table->dropColumn("display_on_arrivals");
        });
    }
};
