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
        Schema::connection('tandc_live')->create('inbound_container_approval', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id");
            $table->integer("container_id");
            $table->integer("file_id")->nullable(true);
            $table->string("comments")->nullable(true);
            $table->boolean("approved")->default(false);
            $table->timestamps();
        });
        Schema::connection('tandc_live')->create('files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // storage filename
            $table->string('original_name'); // original filename
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
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
        Schema::connection('tandc_live')->dropIfExists('inbound_container_approval');
        Schema::connection('tandc_live')->dropIfExists('files');
    }
};
