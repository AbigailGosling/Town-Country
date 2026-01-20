<?php

use App\Models\Species;
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
        Schema::connection('tandc_live')->table('species', function (Blueprint $table) {
            $table->boolean("show_comments")->default(false);
        });
        $s = Species::where("name","Pork")->first();
        $s->show_comments = true;
        $s->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('species', function (Blueprint $table) {
            $table->dropColumn("show_comments");
        });
    }
};
