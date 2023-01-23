<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    /**
     * Get Correctly Ordered list of Permissions for render
     *
     * @return Illuminate\Support\Collection
     */
    public static function GetPermissionList()
    {
        return Permission::where("name","<>","superadmin")->orderBy("group")->get();
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
