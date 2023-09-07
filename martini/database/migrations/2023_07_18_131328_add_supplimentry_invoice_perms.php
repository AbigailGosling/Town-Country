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
        $oldPerm = new PagePermission();
        $newPerm = new Permission();
        $oldPerm->name = '<span class="small">Supplementry</span> Invoice / Credits';
        $newPerm->label = $newPerm->description = "Supplementry Invoice / Credits";
        $oldPerm->column = $newPerm->group = 1;
        $oldPerm->file = $newPerm->name =  $newPerm->file = "supplementryInvoice.php";
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
        $newPerm = Permission::where("name","supplementryInvoice.php")->first();
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $oldPerm->forceDelete();
    }
};
