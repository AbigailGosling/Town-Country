<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserPermission
 * 
 * @property int $permission_id
 * @property int $user_id
 * 
 * @property Permission $permission
 * @property User $user
 *
 * @package App\Models
 */
class UserPermission extends Model
{
	protected $table = 'user_permission';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'permission_id' => 'int',
		'user_id' => 'int'
	];

	public function permission()
	{
		return $this->belongsTo(Permission::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
