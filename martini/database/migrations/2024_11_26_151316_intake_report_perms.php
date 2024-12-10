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
        $oldPerm->name = '<span class="small">Intake</span> Report';
        $newPerm->label = $newPerm->description = "Intake Report";
        $oldPerm->column = $newPerm->group = 3;
        $oldPerm->file = $newPerm->file = "../intake_report/";
        $newPerm->name = "intake_report";
        $newPerm->save();
        $oldPerm->id = $newPerm->id;
        $oldPerm->save();
        User::find(54)->assignPermission($newPerm);
        User::find(5)->assignPermission($newPerm);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $newPerm = Permission::where("name","intake_report")->firstOrFail();

        User::find(54)->unassignPermission($newPerm);
        User::find(5)->unassignPermission($newPerm);

        PagePermission::find($newPerm->id)->firstOrFail()->forceDelete();
        $newPerm->forceDelete();
    }
};
