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
        Schema::connection('tandc_live')->create('health_mark', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('disabled')->default(false);
        });
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->text("import_num")->nullable(true);
            $table->integer("health_id")->default(-1);
            $table->text("internal_num")->nullable(true);
        });

        $newPerm = new Permission();
        $oldPerm = new PagePermission();
        $oldPerm->name = 'Health Marks';
        $newPerm->label = $newPerm->description = "Health Marks";
        $oldPerm->column = $newPerm->group = 3;
        $oldPerm->file = $newPerm->file = "../health_marks";
        $newPerm->name = "health_marks";
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
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->dropColumn("import_num");
            $table->dropColumn("health_mark");
            $table->dropColumn("internal_num");
        });
        Schema::connection('tandc_live')->dropIfExists('health_mark');

        $newPerm = Permission::where("name","health_marks")->first();
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $oldPerm = PagePermission::find($newPerm->id);
        $newPerm->forceDelete();
        $oldPerm->forceDelete();
    }
};
