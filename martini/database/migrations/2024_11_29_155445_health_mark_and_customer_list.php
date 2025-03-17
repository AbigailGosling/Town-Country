<?php

use App\Models\PagePermission;
use App\Models\Permission;
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
        $newPerm = new Permission();
        $oldPerm = new PagePermission();
        $oldPerm->name = '<span class="small">Customer</span> Salesperson';
        $newPerm->label = $newPerm->description = "Customer Salesperson";
        $oldPerm->column = $newPerm->group = 3;
        $oldPerm->file = $newPerm->file = "../usercustomer/";
        $newPerm->name = "usercustomer";
        $newPerm->save();
        $oldPerm->id = $newPerm->id;
        $oldPerm->save();

        Schema::connection('tandc_live')->table('product', function (Blueprint $table) {
            $table->integer('health_id')->default(-1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $newPerm = Permission::where("name","usercustomer")->firstOrFail();
        PagePermission::find($newPerm->id)->firstOrFail()->forceDelete();
        $newPerm->forceDelete();

        Schema::connection('tandc_live')->table('product', function (Blueprint $table) {
            $table->dropColumn('health_id');
        });
    }
};
