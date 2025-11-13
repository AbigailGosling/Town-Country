<?php

use App\Models\PagePermission;
use App\Models\Permission;
use App\Models\User;
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
        $oldPerm->name = 'Inbound Stock';
        $newPerm->label = $newPerm->description = "Inbound Stock";
        $oldPerm->column = $newPerm->group = 1;
        $oldPerm->file = $newPerm->file = "containerreservation.php";
        $newPerm->name = "inbound_stock";
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
        $newPerm = Permission::where("name","inbound_stock")->first();
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user){
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $oldPerm->forceDelete();
    }
};
