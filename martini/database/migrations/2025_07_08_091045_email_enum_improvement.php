<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tandc_live')->
        statement("ALTER TABLE `mail_tracking` CHANGE COLUMN `type` `type` ENUM('STATEMENT','SALES_CONFIRMATION','CREDIT_ALERT','RETRACTION','TEST','SUPPLIER_RETURN','SHORT_STOCK_NOTICE') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
