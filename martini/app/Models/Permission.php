<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Permission
 * 
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string $description
 * @property int $group
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $file
 * 
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Permission extends Model
{
	protected $table = 'permissions';

	protected $casts = [
		'group' => 'int'
	];

	protected $fillable = [
		'name',
		'label',
		'description',
		'group',
		'file'
	];

	public function users(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'user_permission');
	}
	public function group(): BelongsTo
    {
        return $this->belongsTo(PermissionsGroup::class, 'group', 'id');
    }
}
