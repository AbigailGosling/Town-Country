<?php

namespace App\Policies;

use App\Models\CutGroupNationalityDate;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;

class CutGroupNationalityDatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission("cutdates");
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CutGroupNationalityDate  $cutGroupNationalityDate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, CutGroupNationalityDate $cutGroupNationalityDate)
    {
        return $user->hasPermission("cutdates");
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermission("cutdates");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CutGroupNationalityDate  $cutGroupNationalityDate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, CutGroupNationalityDate $cutGroupNationalityDate)
    {
        return $user->hasPermission("cutdates");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CutGroupNationalityDate  $cutGroupNationalityDate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, CutGroupNationalityDate $cutGroupNationalityDate)
    {
        return $user->hasPermission("cutdates");
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CutGroupNationalityDate  $cutGroupNationalityDate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, CutGroupNationalityDate $cutGroupNationalityDate)
    {
        return $user->hasPermission("cutdates");
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CutGroupNationalityDate  $cutGroupNationalityDate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, CutGroupNationalityDate $cutGroupNationalityDate)
    {
        return $user->hasPermission("cutdates");
    }
}
