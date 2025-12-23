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
        $newPerm->label = $newPerm->description =  $newPerm->name = "Can Delete Reservation";
        $newPerm->group = 1;
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
        $newPerm = Permission::where("name","Can Delete Reservation")->first();
        /** @var User $user */
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
    }
};
