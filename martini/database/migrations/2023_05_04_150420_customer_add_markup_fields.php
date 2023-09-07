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
        Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
            $table->enum("markup_type",["FLAT","PERCENT"])->nullable();
            $table->decimal("markup_amount",8,3)->nullable();
        });
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
                $table->dropColumn('markup_type');         
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
