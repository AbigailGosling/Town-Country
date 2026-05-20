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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable();
        });
        $newPerm = new Permission();
        $oldPerm = new PagePermission();
        $oldPerm->name = 'Internal Pallet Movement';
        $newPerm->label = $newPerm->description = 'Internal Pallet Movement';
        $oldPerm->column = $newPerm->group = 2;
        $oldPerm->file = $newPerm->file = '../internal-pallet-movements/';
        $newPerm->name = 'internal-pallet-movement';
        $newPerm->save();
        $oldPerm->id = $newPerm->id;
        $oldPerm->save();

        $newPerm = new Permission();
        $newPerm->description = 'View All Movements';
        $newPerm->label = 'View All Movements';
        $newPerm->group = 2;
        $newPerm->file = '';
        $newPerm->name = 'view-all-internal-pallet-movement';
        $newPerm->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('location_id');
        });
        $newPerm = Permission::where('name', 'internal-pallet-movement')->first();
        $newPerm2 = Permission::where('name', 'view-all-internal-pallet-movement')->first();
        if ($newPerm === null) {
            return;
        }
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user) {
            $user->unassignPermission($newPerm);
            $user->unassignPermission($newPerm2);
        }
        $newPerm->forceDelete();
        $newPerm2->forceDelete();
        $oldPerm->forceDelete();
    }
};
