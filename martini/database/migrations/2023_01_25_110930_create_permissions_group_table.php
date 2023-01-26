<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PermissionsGroup;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        //Migrate all of the Group 0's (administrator privileges) to group 4 for the foreign key
        $permissions = Permission::where('group', 0)->get();
        foreach($permissions as $permission)
        {
            $permission->group = 4;
            $permission->save();
        }


        PermissionsGroup::insert([[
            'name' => 'Sales'
        ],[
            'name' => 'Intake'
        ],[
            'name' => 'Stock'
        ],[
            'name' => 'Administration'
        ]]);

        //Manually add the foreign key constraint once the permissions groups has been populated
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions_groups');
    }
};
