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
        Schema::connection('tandc_live')->table('pickersheets', function (Blueprint $table) {
            $table->string('receiver_name')->nullable();
            $table->unsignedBigInteger('signature_file_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('pickersheets', function (Blueprint $table) {
            $table->dropColumn('receiver_name');
            $table->dropColumn('signature_file_id');
        });
    }
};
