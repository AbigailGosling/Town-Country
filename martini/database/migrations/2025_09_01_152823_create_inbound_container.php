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
        Schema::connection('tandc_live')->create('inbound_container', function (Blueprint $table) {
            $table->id();
            $table->string("internal_number");
            $table->string("origin_port");
            $table->date("eta");
            $table->boolean("admin_approved")->default(false)->index();
            $table->boolean("arrived")->default(false)->index();
            $table->timestamps();
        });
        Schema::connection('tandc_live')->create('container_products', function (Blueprint $table) {
            $table->id();
            $table->integer("container_id")->index();
            $table->integer("product_id")->index();
            $table->index(["container_id","product_id"]);
        });
        $users = User::whereIn('id',[54,99])->get();
        $newPerm = new Permission();
        $oldPerm = new PagePermission();
        $oldPerm->name = '<span class="small">Manage</span> Containers';
        $newPerm->label = $newPerm->description = "Manage Containers";
        $oldPerm->column = $newPerm->group = 2;
        $oldPerm->file = $newPerm->file = "../containers/";
        $newPerm->name = "containers";
        $newPerm->save();
        $oldPerm->id = $newPerm->id;
        $oldPerm->save();
        foreach ($users as $user) $user->assignPermission($newPerm);

        $newPerm = new Permission();
        $newPerm->label = $newPerm->description = "Container Signoff";
        $newPerm->group = 2;
        $newPerm->name = "container_signoff";
        $newPerm->file = "";
        $newPerm->save();
        foreach ($users as $user) $user->assignPermission($newPerm);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->dropIfExists('inbound_container');
        Schema::connection('tandc_live')->dropIfExists('container_products');

        $newPerm = Permission::where("name","containers")->first();
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user){
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $oldPerm->forceDelete();

        $newPerm = Permission::where("name","container_signoff")->first();
        foreach (User::all() as $user){
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
    }
};
