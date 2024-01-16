<?php

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        $newPerm->label = $newPerm->description = "View Product ID";
        $newPerm->group = 1;
        $newPerm->name = "view_product_id_on_intake";
        $newPerm->file = "";
        $newPerm->save();
        User::find(54)->assignPermission($newPerm);
        User::find(5)->assignPermission($newPerm);
        DB::select(DB::raw("UPDATE `tandc_live`.`comment_logging` SET `type` = 'product_note' WHERE `type` = 'product_weightnote'"));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $newPerm = Permission::where("name","view_product_id_on_intake")->first();
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
    }
};
