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
        $oldPerm->name = 'Driver PODs';
        $newPerm->label = $newPerm->description = 'Driver PODs';
        $oldPerm->column = $newPerm->group = 2;
        $oldPerm->file = $newPerm->file = 'https://driver.barracudamobile.co.uk/';
        $newPerm->name = 'driver_pods';
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
        $newPerm = Permission::where('name', 'driver_pods')->first();
        if ($newPerm === null) {
            return;
        }

        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user) {
            $user->unassignPermission($newPerm);
        }

        $newPerm->forceDelete();

        if ($oldPerm !== null) {
            $oldPerm->forceDelete();
        }
    }
};
