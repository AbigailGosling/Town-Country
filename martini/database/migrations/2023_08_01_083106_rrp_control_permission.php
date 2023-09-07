<?php

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
        $newPerm->label = $newPerm->description = "View Costs";
        $newPerm->group = 3;
        $newPerm->name = "viewcosts";
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
        $newPerm = Permission::where("name","viewcosts")->first();
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
    }
};
