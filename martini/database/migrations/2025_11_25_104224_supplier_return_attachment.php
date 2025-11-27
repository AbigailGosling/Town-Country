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
        Schema::connection("tandc_live")->create("supplier_return_attachment",function(Blueprint $table)
        {
            $table->id();
            $table->integer("user_id");
            $table->integer("return_id");
            $table->integer("file_id")->nullable(true);
            $table->string("comments")->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection("tandc_live")->dropIfExists("supplier_return_attachment");
    }
};
