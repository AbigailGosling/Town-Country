<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property bool $use_two_factor
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property Carbon|null $two_factor_expires_at
 * @property bool $disabled
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $hash_method
 * @property bool $override_saledate_check
 * @property string|null $actual_email
 * @property bool $is_hidden
 * @property bool $receive_short_stock
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
        'disabled',
        'is_hidden',
        'use_two_factor',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_expires_at',
        'actual_email',
        'receive_short_stock',
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
        'disabled' => 'bool',
        'is_hidden' => 'bool',
        'use_two_factor' => 'bool',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
    ];
    public function generateTwoFactorCode()
    {
        $this->timestamps = false;
        $this->two_factor_secret = strtoupper(Str::random(5));
        $this->two_factor_expires_at = now()->addMinutes(10);
        $this->save();
    }
    public function resetTwoFactorCode()
    {
        $this->timestamps = false;
        $this->two_factor_secret = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }
    public function routeNotificationForMail(){
        $email = "";
        if ($this->actual_email && filter_var($this->actual_email, FILTER_VALIDATE_EMAIL)) $email = $this->actual_email;
        else $email = $this->email;
        return $email;
    }
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
        return in_array($customer_id,$this->listViewableCustomers());
    }
    private $_listViewableCustomers = null;
    public function listViewableCustomers():array
    {
        if ($this->_listViewableCustomers === null)
        {
            if ($this->hasPermission("restrictedaccess") == false)
            $this->_listViewableCustomers = Customer::all()->pluck('id')->toArray();
            else
            {
                $users = ActiveHolidayCover::where("cover_id",$this->id)->pluck('absent_id')->toArray() ?? [];
                $users[] = $this->id;
                $this->_listViewableCustomers = Customer::whereIn('default_salesman_id',$users)->orWhereIn('default_finance_person_id',$users)->pluck('id')->toArray();
            }
        }
        return $this->_listViewableCustomers;
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
    public function internal_user():HasOne{
        return $this->hasOne(OldUser::class,"id","id");
    }
}
