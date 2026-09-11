<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::connection('tandc_live')->table('pickersheet_documents', function (Blueprint $table) {
            $table->string('driver_name')->nullable();
            $table->timestamp('device_timestamp')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`pickersheet_documents` CHANGE COLUMN `created_at` `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`pickersheet_documents` CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('pickersheet_documents', function (Blueprint $table) {
            $table->dropColumn('device_timestamp');
            $table->dropColumn('driver_name');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
};
