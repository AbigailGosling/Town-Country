<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Exception;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class,'user_permission');
    }
    public function assignPermission(Permission $permission)
    {
        if (!$this->hasPermission($permission))
        {
            return $this->permissions()->attach($permission->id);
        }
    }
    public function unassignPermission(Permission $permission)
    {
        if ($this->hasPermission($permission))
        {
            return $this->permissions()->detach($permission->id);
        }
    }
    public function hasPermissionClarified(Permission $permission)
    {
        return ($this->permissions()->get()->contains('id',"=",$permission->id));
    }
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->hasPermissionClarified(Permission::where('name', $permission)->first());
        }
        else if ($permission instanceof Permission){
            return $this->hasPermissionClarified($permission);
        }
        
    }
    public function isAdmin()
    {
        return ($this->hasPermission("admin") || $this->isSuperAdminElevated());
    }
    public function isSuperAdminElevated()
    {
        return ($this->hasPermission("superadmin") && session()->has('superAdminElevated') && session()->get('superAdminElevated') == true);
    }
    public function toggleSuperAdminMode()
    {
        if ($this->hasPermission("superadmin"))
        {
            $internaltest = false;
            if (session()->has('superAdminElevated')) $internaltest = session()->get('superAdminElevated');
            session()->put('superAdminElevated',!$internaltest);
        }
    }
}
