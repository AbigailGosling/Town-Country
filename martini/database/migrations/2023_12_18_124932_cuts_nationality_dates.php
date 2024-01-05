<?php

use App\Models\Cut;
use App\Models\CutGroup;
use App\Models\CutGroupNationalityDate;
use App\Models\Nationality;
use App\Models\PagePermission;
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
        DB::connection("tandc_live")->unprepared("ALTER TABLE `tandc_live`.`cutgroups` ENGINE = InnoDB;");
        DB::connection("tandc_live")->unprepared("ALTER TABLE `tandc_live`.`nationality` ENGINE = InnoDB;");
        Schema::connection("tandc_live")->create('cutgroup_nationality_dates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cutgroup_id')->index();
            $table->integer('nationality_id')->index();
            $table->integer('warning')->unsigned();
            $table->integer('danger')->unsigned();
            $table->unique(['cutgroup_id', 'nationality_id']);
        });
        Schema::connection("tandc_live")->table('cutgroup_nationality_dates', function (Blueprint $table) {
            $table->foreign('cutgroup_id',"cutgroup")->references('id')->on('cutgroups')->onDelete('cascade');
            $table->foreign('nationality_id',"nationality")->references('id')->on('nationality')->onDelete('cascade');
        });
        $cuts = Cut::all();
        $nationalities = Nationality::all();
        foreach ($cuts as $cut){
            if (((is_numeric($cut->warning) && $cut->warning > 0) || (is_numeric($cut->danger) && $cut->danger > 0)) && CutGroup::find($cut->cutgroup_id))
            {
                foreach ($nationalities as $nationality){
                    if ($nationality->name == "") continue;
                    $cgnd = CutGroupNationalityDate::firstOrCreate(['cutgroup_id'=>$cut->cutgroup_id,'nationality_id'=>$nationality->id]);
                    if ($cut->warning > $cgnd->warning) $cgnd->warning = $cut->warning;
                    if ($cut->danger > $cgnd->danger) $cgnd->danger = $cut->danger;
                    $cgnd->save();
                }
            }
        }
        CutGroupNationalityDate::where(["warning"=>0,"danger"=>0])->delete();
        $oldPerm = new PagePermission();
        $newPerm = new Permission();
        $oldPerm->name = '<span class="small">Manage</span> Cut Group Date Ranges';
        $newPerm->label = $newPerm->description = "Manage  Cut Group Date Ranges";
        $oldPerm->column = $newPerm->group = 3;
        $oldPerm->file = $newPerm->file = "../cutdates";
        $newPerm->name = "cutdates";
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
        Schema::connection("tandc_live")->dropIfExists('cutgroup_nationality_dates');
        $newPerm = Permission::where("name","cutdates")->first();
        $oldPerm = PagePermission::find($newPerm->id);
        foreach (User::all() as $user)
        {
            $user->unassignPermission($newPerm);
        }
        $newPerm->forceDelete();
        $oldPerm->forceDelete();
    }
};
