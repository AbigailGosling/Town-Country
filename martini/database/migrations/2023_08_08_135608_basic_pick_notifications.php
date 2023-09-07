<?php

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
        Schema::connection('tandc_live')->create('pickerNotifications', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('pickersheet_id')->unsigned()->index();
            $table->string('message');
            $table->boolean('locked');
            $table->boolean('lock_release')->default(false);
            $table->timestamps();
        });
        $newPerm = new Permission();
        $newPerm->label = $newPerm->description = "Send Notifications to Picksheets";
        $newPerm->group = 2;
        $newPerm->name = "send_picker_notification";
        $newPerm->file = "";
        $newPerm->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pickerNotifications');
        $newPerm = Permission::where("name","viewcosts")->first();
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
    }
};
