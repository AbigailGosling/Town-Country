<?php

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
        Schema::connection("tandc_live")->table("client_addresses",function(Blueprint $table){
            $table->dropColumn("restrictions");

            $table->string("allowed_vehicle_types")->default("");
            $table->boolean("require_tail_lift")->default(false);
            $table->boolean("require_fork_lift")->default(false);
            $table->time("opening_time")->nullable();
            $table->time("closing_time")->nullable();
            $table->boolean("open_bank_holiday_mondays")->default(false);
            $table->boolean("open_bank_holiday_fridays")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("tandc_live")->table("client_addresses",function(Blueprint $table){
            $table->text("restrictions")->nullable();
            $table->dropColumn("allowed_vehicle_types");
            $table->dropColumn("require_tail_lift");
            $table->dropColumn("require_fork_lift");
            $table->dropColumn("opening_time");
            $table->dropColumn("closing_time");
            $table->dropColumn("open_bank_holiday_mondays");
            $table->dropColumn("open_bank_holiday_fridays");
        });
    }
};
