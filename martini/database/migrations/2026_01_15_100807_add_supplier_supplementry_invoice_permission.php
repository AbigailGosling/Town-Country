<?php

use App\Models\PagePermission;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

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
        $oldPerm->name = '<span class="small">Supplementry Supplier</span> Inv / Crds';
        $newPerm->label = $newPerm->description = "Supplementry Supplier Inv / Crds";
        $oldPerm->column = $newPerm->group = 1;
        $oldPerm->file = $newPerm->file = "supplementrySupplierInvoice.php";
        $newPerm->name = "supplementrySupplierInvoice.php";
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
        $newPerm = Permission::where("name","supplementrySupplierInvoice.php")->first();
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user){
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $oldPerm->forceDelete();
    }
};
