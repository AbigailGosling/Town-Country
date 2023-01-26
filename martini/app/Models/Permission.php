<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\PermissionsGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(PermissionsGroup::class, 'group', 'id');
    }
}
