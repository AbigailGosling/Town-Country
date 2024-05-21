<?php

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
        $newPerm->label = $newPerm->description = "Change Order Reference";
        $newPerm->group = 1;
        $newPerm->name = "change_sale_reference";
        $newPerm->file = "";
        $newPerm->save();
        User::find(54)->assignPermission($newPerm);
        User::find(5)->assignPermission($newPerm);

        $newPerm = new Permission();
        $newPerm->label = $newPerm->description = "View All Confirmations";
        $newPerm->group = 1;
        $newPerm->name = "view_all_sale_confirmations";
        $newPerm->file = "";
        $newPerm->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $newPerm = Permission::where("name","change_sale_reference")->first();
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $newPerm = Permission::where("name","view_all_sale_confirmations")->first();
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
    }
};
