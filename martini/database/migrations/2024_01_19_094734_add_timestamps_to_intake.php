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
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->boolean('deleted')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
        Schema::connection('tandc_live')->table('pallet', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
        Schema::connection('tandc_live')->table('product', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
        Schema::connection('tandc_live')->table('weights', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
        DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_intake_trigger`;
        CREATE TRIGGER `insert_intake_trigger` BEFORE INSERT ON `intake`
         FOR EACH ROW SET NEW.`created_at` = NOW();
         ');
         DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_pallet_trigger`;
        CREATE TRIGGER `insert_pallet_trigger` BEFORE INSERT ON `pallet`
         FOR EACH ROW SET NEW.`created_at` = NOW();
         ');
         DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_product_trigger`;
        CREATE TRIGGER `insert_product_trigger` BEFORE INSERT ON `product`
         FOR EACH ROW SET NEW.`created_at` = NOW();
         ');
         DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_weights_trigger`;
        CREATE TRIGGER `insert_weights_trigger` BEFORE INSERT ON `weights`
         FOR EACH ROW SET NEW.`created_at` = NOW();
         ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->dropColumn('deleted');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
        Schema::connection('tandc_live')->table('pallet', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
        Schema::connection('tandc_live')->table('product', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
        Schema::connection('tandc_live')->table('weights', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
        DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_intake_trigger`;');
        DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_pallet_trigger`;');
        DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_product_trigger`;');
        DB::connection('tandc_live')->unprepared('DROP TRIGGER IF EXISTS `insert_weights_trigger`;');
    }
};
