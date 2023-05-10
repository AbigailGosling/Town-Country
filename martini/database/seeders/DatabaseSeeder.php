<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;
use App\Models\PagePermission;
use App\Models\OldUser;
use Exception;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (PagePermission::all() as $oldPermission)
        {
            $perm = new Permission;
            $perm->id = $oldPermission->id;
            $perm->label =  str_replace('<span class="small">','',str_replace('</span>','',$oldPermission->name));
            $perm->description =  str_replace('<span class="small">','',str_replace('</span>','',$oldPermission->name));
            $perm->group =  $oldPermission->column;
            $existing = Permission::where('name',$oldPermission->file)->first();
            if (!$existing)
            {
                $perm->name = $oldPermission->file;
                $perm->file = $oldPermission->file;
                $perm->save();
            }
            if ($perm->label == "editUsers.php")
            {
                $oldPermission->file = "../users";
                $oldPermission->save();
                $perm->file = "../users";
                $perm->save();
            }
        }

        $perm = new Permission;
        $perm->label = 'Show Intake Overview Table Prices';
        $perm->description = 'Show Intake Overview Table Prices';
        $perm->group = 4;
        $perm->file = $perm->name = 'view_intake_prices';
        $perm->save();

        $perm = new Permission;
        $perm->label = 'Ability to change salesman on create sale';
        $perm->description = 'Ability to change salesman on create sale';
        $perm->group = 4;
        $perm->file = $perm->name = 'allow_override_salesman';
        $perm->save();

        $perm = new Permission;
        $perm->label = 'Admin';
        $perm->description = 'Admin';
        $perm->group = 4;
        $perm->file = $perm->name = 'admin';
        $perm->save();

        $perm = new Permission;
        $perm->label = 'Set Prices';
        $perm->description = 'set_prices';
        $perm->group = 4;
        $perm->file = $perm->name = 'set_prices';
        $perm->save();

        $perm = new Permission;
        $perm->label = 'Super Admin';
        $perm->description = 'Super Admin';
        $perm->group = 0;
        $perm->file = $perm->name = 'superadmin';
        $perm->save();

        foreach (OldUser::all() as $oldUser)
        {
            $user =  new User;
            $user->id = $oldUser->id;
            $user->name = $oldUser->name;
            $user->email = $oldUser->email;
            if (strpos(strtolower($user->name),"removed")> 0)
            {
                $user->disabled = true;
                $user->email = $user->email.$user->id;
            }
            $user->password = $oldUser->password;
            $user->hash_method = "SHA1";
            $user->saveQuietly();
            $pp = explode(",",$oldUser->pages);
            foreach($pp as $oldPage)
            {
                $perm = Permission::find($oldPage);
                $oldp = PagePermission::find($oldPage);
                if ($perm)
                {
                    $user->assignPermission($perm);
                }
                else if ($oldp)
                {
                    $perm = Permission::where("name",$oldp->page)->first();
                    if ($perm)
                    {
                        $user->assignPermission($perm);
                    }
                }
            }
            if ($oldUser->view_intake_prices == 1)
            {
                $user->assignPermission(Permission::where('name','view_intake_prices')->first());
            }
            if ($oldUser->allow_override_salesman == 1)
            {
                $user->assignPermission(Permission::where('name','allow_override_salesman')->first());
            }
            if ($oldUser->user_type == "A")
            {
                $user->assignPermission(Permission::where('name','admin')->first());
            }
            if ($oldUser->id == 54)
            {
                $user->assignPermission(Permission::where('name','superadmin')->first());
            }
        }
    }
}
