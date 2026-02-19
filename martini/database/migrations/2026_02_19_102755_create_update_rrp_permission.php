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
        $newPerm->label = $newPerm->description = "Update MSRP/RRP";
        $newPerm->group = 3;
        $newPerm->name = "update_rrp";
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
        $perm = Permission::where('name', 'update_rrp')->first();
        if ($perm) {
            User::all()->each(function (User $user) use ($perm) {
                $user->unassignPermission($perm);
            });
            $perm->delete();
        }
    }
};
