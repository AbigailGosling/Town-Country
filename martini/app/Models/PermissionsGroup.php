<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class PermissionsGroup
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class PermissionsGroup extends Model
{
	protected $table = 'permissions_groups';

	protected $fillable = [
		'name'
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
