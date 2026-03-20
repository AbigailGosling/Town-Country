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
        $oldPerm->name = '<span class="small">Outgoing</span> Pallet Management';
        $newPerm->label = $newPerm->description = "Outgoing Pallet Management";
        $oldPerm->column = $newPerm->group = 2;
        $oldPerm->file = $newPerm->file = "../outgoing-pallets/";
        $newPerm->name = "outgoingPalletManagement";
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
        $newPerm = Permission::where("name","outgoingPalletManagement")->first();
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user){
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $oldPerm->forceDelete();
    }
};
