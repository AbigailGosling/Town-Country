<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionsGroup extends Model
{
    protected $fillable = [
        'name',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'group', 'id');
    }
}
