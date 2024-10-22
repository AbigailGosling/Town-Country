<?php

namespace App\Policies;

use App\Models\HealthMark;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;

class HealthMarkPolicy
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
        return $user->hasPermission("health_marks");
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HealthMark  $HealthMark
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, HealthMark $HealthMark)
    {
        return $user->hasPermission("health_marks");
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermission("health_marks");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HealthMark  $HealthMark
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, HealthMark $HealthMark)
    {
        return $user->hasPermission("health_marks");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HealthMark  $HealthMark
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, HealthMark $HealthMark)
    {
        return $user->hasPermission("health_marks");
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HealthMark  $HealthMark
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, HealthMark $HealthMark)
    {
        return $user->hasPermission("health_marks");
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HealthMark  $HealthMark
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, HealthMark $HealthMark)
    {
        return $user->hasPermission("health_marks");
    }
}
