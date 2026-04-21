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
        $newPerm = new Permission();
        $oldPerm = new PagePermission();
        $oldPerm->name = 'Credit Exposure Report';
        $newPerm->label = $newPerm->description = 'Credit Exposure Report';
        $oldPerm->column = $newPerm->group = 3;
        $oldPerm->file = $newPerm->file = '../insuredcreditreport/';
        $newPerm->name = 'insuredCreditReport';
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
        $newPerm = Permission::where('name', 'insuredCreditReport')->first();
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
