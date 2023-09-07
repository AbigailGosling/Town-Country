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
        Schema::connection('tandc_live')->table('pickerSheets', function (Blueprint $table) {
            $table->boolean("isSupplemental")->default(false)->nullable(false);
        });
        try {
            DB::connection('tandc_live')->insert("INSERT INTO `tandc_live`.`intake` (`id`, `returned`, `supplier_id`, `purchase_id`, `vehicle_reg`, `vehicle_temp`, `delivery_note_number`, `user_id`, `product_temperature`, `vehicle_temperature`, `date_received`, `security_id`, `notes`, `date_paid`) VALUES ('-1', '0', '-1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);");
            DB::connection('tandc_live')->insert("INSERT INTO `tandc_live`.`pallet` (`id`, `intake_id`, `comments`, `storage_location`, `grosspallet`, `gross_weight`, `pallet_tare`, `tare_per_carton`, `number_of_cartons`, `net_weight`, `user_id`) VALUES ('-1', '-1', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL)");
            DB::connection('tandc_live')->insert("INSERT INTO `tandc_live`.`supplier` (`id`, `name`, `postcode`, `contact_number`, `contact_name`, `user_id`, `internal_number`) VALUES ('-1', NULL, NULL, NULL, NULL, NULL, NULL)");
        }
        catch (\Exception $ex){

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('pickerSheets', function (Blueprint $table) {
            $table->dropColumn("isSupplemental");
        });
    }
};
