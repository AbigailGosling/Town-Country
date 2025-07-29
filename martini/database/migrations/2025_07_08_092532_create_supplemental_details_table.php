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
        try
        {Schema::connection("tandc_live")->create('supplemental_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("pickersheet_id")->index();
            $table->text("reference");
            $table->timestamps();
        });}
        catch(\Exception $e){}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("tandc_live")->dropIfExists('supplimental_details');
    }
};
