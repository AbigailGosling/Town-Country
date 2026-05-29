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
        Schema::connection('tandc_live')->create('intake_scanning_file', function (Blueprint $table) {
            $table->id();
            $table->integer('intake_id')->nullable(true);
            $table->string('upload_session_id');
            $table->integer('file_id')->nullable(true);
            $table->integer('sequence')->default(0);
            $table->string('file_role', 20)->default('image');
            $table->integer('source_file_id')->nullable(true);
            $table->longText('json_payload')->nullable(true);
            $table->text('error_message')->nullable(true);
            $table->timestamp('processed_at')->nullable(true);
            $table->timestamps();

            $table->index(['upload_session_id', 'file_role', 'sequence'], 'intakescanningfile_session_role_sequence_idx');
            $table->index(['intake_id'], 'intakescanningfile_intake_idx');
            $table->index(['source_file_id'], 'intakescanningfile_source_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->dropIfExists('intake_scanning_file');
    }
};
