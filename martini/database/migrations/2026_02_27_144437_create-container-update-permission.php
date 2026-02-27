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
        $newPerm->label = $newPerm->description = "Update Container Prices After Intake";
        $newPerm->group = 3;
        $newPerm->name = "update_container";
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
        $perm = Permission::where('name', 'update_container')->first();
        if ($perm) {
            User::all()->each(function (User $user) use ($perm) {
                $user->unassignPermission($perm);
            });
            $perm->delete();
        }
    }
};
