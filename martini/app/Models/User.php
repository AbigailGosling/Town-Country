<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Exception;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property bool $disabled
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $hash_method
 * 
 * @property Collection|Permission[] $permissions
 *
 * @package App\Models
 */
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

	protected $dates = [
		'email_verified_at'
	];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'disabled' => 'bool'
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
    public function canViewCustomer(int $customer_id)
    {
        if ($this->hasPermission("restrictedaccess") == false) return true;
        //return Customer;
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
