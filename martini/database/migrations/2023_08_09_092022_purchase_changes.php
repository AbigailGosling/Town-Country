<?php

use App\Models\PagePermission;
use App\Models\Permission;
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
        Schema::connection('tandc_live')->table('purchase_form', function (Blueprint $table) {
            $table->integer('site_id')->unsigned()->default(0)->index();
        });
        $newPerm = Permission::find(3);
        $oldPerm = PagePermission::find(3);
        $newPerm->label = $newPerm->description = "create New Arrival";
        $newPerm->save();

        $oldPerm->name = '<span class="small">create</span> New Arrival';
        $oldPerm->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
