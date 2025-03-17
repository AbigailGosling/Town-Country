<?php

use App\Models\PagePermission;
use App\Models\Permission;
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
        Schema::connection('tandc_live')->create('supplier_returns', function (Blueprint $table) {
            $table->id("id");
            $table->integer("user_id")->index();
            $table->integer("supplier_id")->index();
            $table->integer("pick_id")->index();
            $table->text("reference_number")->nullable(true);
            $table->text("comments")->nullable(true);
            $table->boolean("deleted")->default(false);
            $table->timestamps();
        });
        Schema::connection('tandc_live')->create('supplier_return_items', function (Blueprint $table) {
            $table->id("id");
            $table->integer("supplier_return_id")->index();
            $table->integer("product_id")->index();
            $table->integer("cases",false,true);
            $table->boolean("deleted")->default(false);
            $table->timestamps();
        });
        if (Schema::connection('tandc_live')->hasColumn('pickersheets','is_return_to_supplier')==false) Schema::connection('tandc_live')->table('pickerSheets',function (Blueprint $table){
            $table->boolean('is_return_to_supplier')->default(false);
        });
        if (Schema::connection('tandc_live')->hasColumn('supplier','address_1')==false){
                Schema::connection('tandc_live')->table('supplier',function (Blueprint $table){
                $table->text('address_1')->nullable(true)->after('name');
                $table->text('address_2')->nullable(true)->after('address_1');
                $table->text('address_3')->nullable(true)->after('address_2');
                $table->text('address_4')->nullable(true)->after('address_3');
                $table->text('email')->nullable(true)->after('postcode');
            });
        }
        Schema::connection('tandc_live')->table('mail_tracking',function (Blueprint $table){
            $table->timestamp("last_update")->default("CURRENT_TIMESTAMP")->change();
        });
        //THIS BLOCK MAY NEED TO BE RAN MANUALLY AND REMOVED FROM THE MIGRATION
        DB::connection('tandc_live')->statement("SET SQL_SAFE_UPDATES = 0;");
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`mail_tracking` CHANGE COLUMN `last_update` `last_update` TIMESTAMP NULL;");
        DB::connection('tandc_live')->statement("UPDATE `tandc_live`.`mail_tracking` SET `last_update` = NULL WHERE CAST(`last_update` AS CHAR(20)) = '0000-00-00 00:00:00';");
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`mail_tracking` CHANGE COLUMN `type` `type` ENUM('STATEMENT','SALES_CONFIRMATION','CREDIT_ALERT','RETRACTION','TEST','SUPPLIER_RETURN') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`mail_tracking` CHANGE COLUMN `last_update` `last_update` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");

        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`supplier_returns` CHANGE COLUMN `created_at` `created-at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`supplier_returns` CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");

        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`supplier_return_items` CHANGE COLUMN `created_at` `created-at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;");
        DB::connection('tandc_live')->statement("ALTER TABLE `tandc_live`.`supplier_return_items` CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");

        DB::connection('tandc_live')->statement("SET SQL_SAFE_UPDATES = 1;");
        //END BLOCK
        $newPerm = new Permission();
        $oldPerm = new PagePermission();
        $oldPerm->name = '<span class="small">Supplier</span> Return';
        $newPerm->label = $newPerm->description = "Supplier Return";
        $oldPerm->column = $newPerm->group = 1;
        $oldPerm->file = $newPerm->file = "supplierreturn.php";
        $newPerm->name = "supplierreturn.php";
        $newPerm->save();
        $oldPerm->id = $newPerm->id;
        $oldPerm->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $newPerm = Permission::where("name","supplierreturn.php")->firstOrFail();
        PagePermission::find($newPerm->id)->firstOrFail()->forceDelete();
        $newPerm->forceDelete();

        Schema::connection('tandc_live')->dropIfExists('supplier_returns');
        Schema::connection('tandc_live')->dropIfExists('supplier_return_items');
    }
};
