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
        $oldPerm->name = '<span class="small">Create</span> Collection/Return';
        $newPerm->label = $newPerm->description = 'Create Collection/Return';
        $oldPerm->column = $newPerm->group = 1;
        $oldPerm->file = $newPerm->file = 'collectionReturns.php';
        $newPerm->name = 'createCollection';
        $newPerm->save();
        $oldPerm->id = $newPerm->id;
        $oldPerm->save();

        $newPerm = new Permission();
        $oldPerm = new PagePermission();
        $oldPerm->name = '<span class="small">Manage</span> Collection/Return';
        $newPerm->label = $newPerm->description = "Manage Collection/Return";
        $oldPerm->column = $newPerm->group = 2;
        $oldPerm->file = $newPerm->file = "collectionReturnsList.php";
        $newPerm->name = "manageCollection";
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
        $newPerm = Permission::where('name', 'createCollection')->first();
        if ($newPerm === null) {
            return;
        }

        $newPerm2 = Permission::where('name', 'manageCollection')->first();
        if ($newPerm2 === null) {
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

        $oldPerm2 = PagePermission::find($newPerm2->id);
        foreach (User::all() as $user) {
            $user->unassignPermission($newPerm2);
        }

        $newPerm2->forceDelete();

        if ($oldPerm2 !== null) {
            $oldPerm2->forceDelete();
        }
    }
};
