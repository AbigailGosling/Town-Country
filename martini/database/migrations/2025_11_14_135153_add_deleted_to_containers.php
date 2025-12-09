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
        Schema::connection('tandc_live')->table('inbound_container', function (Blueprint $table) {
            $table->boolean("deleted")->default(false);
        });
        Schema::connection('tandc_live')->table('container_products', function (Blueprint $table) {
            $table->boolean("deleted")->default(false);
        });
        Schema::connection('tandc_live')->table('reservation', function (Blueprint $table) {
            $table->boolean("deleted")->default(false);
            $table->boolean("sent")->default(false);
            $table->boolean("processed")->default(false);
        });
        Schema::connection('tandc_live')->table('reservation_product', function (Blueprint $table) {
            $table->boolean("deleted")->default(false);
        });
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`mail_tracking` CHANGE COLUMN `type` `type` ENUM('STATEMENT','SALES_CONFIRMATION','CREDIT_ALERT','RETRACTION','TEST','SUPPLIER_RETURN','SHORT_STOCK_NOTICE','RESERVATION') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('inbound_container', function (Blueprint $table) {
            $table->dropColumn("deleted");
        });
        Schema::connection('tandc_live')->table('container_products', function (Blueprint $table) {
            $table->dropColumn("deleted");
        });
        Schema::connection('tandc_live')->table('reservation', function (Blueprint $table) {
            $table->dropColumn("deleted");
            $table->dropColumn("sent");
            $table->dropColumn("processed");

        });
        Schema::connection('tandc_live')->table('reservation_product', function (Blueprint $table) {
            $table->dropColumn("deleted");
        });
        DB::connection('tandc_live')->statement("ALTER TABLE `mail_tracking` CHANGE COLUMN `type` `type` ENUM('STATEMENT','SALES_CONFIRMATION','CREDIT_ALERT','RETRACTION','TEST','SUPPLIER_RETURN','SHORT_STOCK_NOTICE') NOT NULL");;
    }
};
