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
        Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
            $table->boolean("delivery_day_checking")->default(false);
            $table->boolean("delivery_day_override")->default(false);
            $table->tinyInteger("delivery_days")->unsigned()->default(0);
        });
        $oldPerm = new PagePermission();
        $newPerm = new Permission();
        $oldPerm->name = '<span class="small">Customer</span> Overrides';
        $newPerm->label = $newPerm->description = "Customer Overrides";
        $oldPerm->column = $newPerm->group = 3;
        $oldPerm->file = $newPerm->name =  $newPerm->file = "../customers/overrides";
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
        Schema::connection('tandc_live')->table('customers', function (Blueprint $table) {
            $table->dropColumn("delivery_day_checking");
            $table->dropColumn("delivery_day_override");
            $table->dropColumn("delivery_days");
        });
        $newPerm = Permission::where("name","../customers/overrides")->first();
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $oldPerm->forceDelete();
    }
};
