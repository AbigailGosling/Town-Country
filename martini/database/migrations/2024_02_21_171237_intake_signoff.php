<?php

use App\Models\Intake;
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
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->boolean('approved')->default(false);
            $table->integer('approved_by')->nullable();
            $table->timestamp('approved_date')->nullable();
        });
        $newPerm = new Permission();
        $newPerm->file = "";
        $newPerm->label = $newPerm->description = "Can Approve Intake";
        $newPerm->group = 2;
        $newPerm->name = "approve_intake";
        $newPerm->save();
        User::find(54)->assignPermission($newPerm);
        User::find(5)->assignPermission($newPerm);
        Intake::query()->update(['approved'=>true,'approved_by'=>-1,'approved_date'=>new DateTime()]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tandc_live')->table('intake', function (Blueprint $table) {
            $table->dropColumn('approved');
            $table->dropColumn('approved_by');
            $table->dropColumn('approved_date');
        });
        $newPerm = Permission::where("name","approve_intake")->first();
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
    }
};
