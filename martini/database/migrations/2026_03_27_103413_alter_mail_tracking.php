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
        DB::connection('tandc_live')->statement("ALTER TABLE `mail_tracking` CHANGE `type` `type` ENUM('STATEMENT', 'SALES_CONFIRMATION', 'CREDIT_ALERT', 'RETRACTION', 'TEST', 'SUPPLIER_RETURN', 'SHORT_STOCK_NOTICE', 'RESERVATION', 'SHORT_PICK', 'PRICE_CHANGE') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //DB::connection('tandc_live')->statement("ALTER TABLE `mail_tracking` CHANGE `type` `type` ENUM('STATEMENT', 'SALES_CONFIRMATION', 'CREDIT_ALERT', 'RETRACTION', 'TEST', 'SUPPLIER_RETURN', 'SHORT_STOCK_NOTICE', 'RESERVATION', 'SHORT_PICK', 'PRICE_CHANGE') NOT NULL");
    }
};
