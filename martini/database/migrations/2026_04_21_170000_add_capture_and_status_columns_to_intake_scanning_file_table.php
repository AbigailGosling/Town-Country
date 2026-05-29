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
        Schema::connection('tandc_live')->table('intake_scanning_file', function (Blueprint $table) {
            $table->integer('user_id')->nullable(true)->after('intake_id');
            $table->boolean('accepted')->default(false)->after('json_payload');
            $table->boolean('deleted')->default(false)->after('accepted');

            $table->index(['user_id'], 'intake_scanning_file_user_idx');
            $table->index(['accepted'], 'intake_scanning_file_accepted_idx');
            $table->index(['deleted'], 'intake_scanning_file_deleted_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('intake_scanning_file', function (Blueprint $table) {
            $table->dropIndex('intake_scanning_file_user_idx');
            $table->dropIndex('intake_scanning_file_accepted_idx');
            $table->dropIndex('intake_scanning_file_deleted_idx');
            $table->dropColumn(['user_id', 'accepted', 'deleted']);
        });
    }
};
