<?php

namespace App\Observers;

use App\Models\OldUser;
use App\Models\PagePermission;
use App\Models\User;
use Exception;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
       $this->createUpdateOldUser($user);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        $this->createUpdateOldUser($user);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //disable
    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        $this->createUpdateOldUser($user);
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //delete
    }
    private function createUpdateOldUser(User $newUser)
    {
        $oldUser = OldUser::firstOrCreate(['id' => $newUser->id]);
        $oldUser->name = $newUser->name;
        $oldUser->email = $newUser->email;
        if ($newUser->disabled == true && strpos(strtolower($oldUser->name),"removed") == 0)
        {
            $oldUser->name = $oldUser->name . " removed";
        }
        $oldUser->password = $newUser->password;
        $pages = [];
        $has_view_intake_prices = false;
        $has_allow_override_salesman = false;
        $user_is_admin = false;
        foreach($newUser->permissions as $newPermission)
        {
            foreach(PagePermission::where("file",$newPermission->file)->get() as $oldp)
            {
                if ($newPermission->group != 0)$pages[] = $oldp->id;
            }           
            if ($newPermission->name == 'view_intake_prices')
            {
                $has_view_intake_prices = true;
            }
            if ($newPermission->name == 'allow_override_salesman')
            {
                $has_allow_override_salesman = true;
            }
            if ($newPermission->name == 'admin')
            {
                $user_is_admin = true;
            }
        }
        $oldUser->view_intake_prices = ($has_view_intake_prices)?1:0;
        $oldUser->allow_override_salesman = ($has_allow_override_salesman)?1:0;
        $oldUser->user_type = ($user_is_admin)?"A":"M";
        $oldUser->pages = implode(",",$pages);
        $oldUser->save();
    }
}
