<?php

use App\Models\PagePermission;
use App\Models\Permission;
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
        $oldPerm->name = '<span class="small">Supplier</span> Return Statements';
        $newPerm->label = $newPerm->description = "Supplier Return Statements";
        $oldPerm->column = $newPerm->group = 3;
        $oldPerm->file = $newPerm->file = "../supplierreturnstatements/";
        $newPerm->name = "supplierreturnstatements";
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
        $newPerm = Permission::where("name","supplierreturnstatements")->firstOrFail();
        PagePermission::find($newPerm->id)->firstOrFail()->forceDelete();
        $newPerm->forceDelete();
    }
};
