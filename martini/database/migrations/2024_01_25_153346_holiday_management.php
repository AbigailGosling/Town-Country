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
        Schema::connection("tandc_live")->create('active_holiday_cover', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('absent_id')->index();
            $table->integer('cover_id')->index();
        });
        $oldPerm = new PagePermission();
        $newPerm = new Permission();
        $oldPerm->name = '<span class="small">Manage</span> Holiday Cover';
        $newPerm->label = $newPerm->description = "Manage Holiday Cover";
        $oldPerm->column = $newPerm->group = 3;
        $oldPerm->file = $newPerm->file = "../holidays";
        $newPerm->name = "holidays";
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
        Schema::connection("tandc_live")->dropIfExists('active_holiday_cover');

        $newPerm = Permission::where("name","holidays")->first();
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $oldPerm->forceDelete();
    }
};
