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
        Schema::connection('tandc_live')->table('invoice_payments', function (Blueprint $table) {
            $table->boolean('deleted')->default(false);
            $table->timestamp('updated_at')->nullable();
            $table->index('deleted');
        });
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`invoice_payments` CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");

        Schema::connection('tandc_live')->table('credit_note_items', function (Blueprint $table) {
            $table->boolean('deleted')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('deleted');
        });
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`credit_note_items` CHANGE COLUMN `created_at` `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`credit_note_items` CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('invoice_payments', function (Blueprint $table) {
            $table->dropColumn('deleted');
            $table->dropColumn('updated_at');
        });
        Schema::connection('tandc_live')->table('credit_note_items', function (Blueprint $table) {
            $table->dropColumn('deleted');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
};
