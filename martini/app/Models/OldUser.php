<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $password
 * @property string $user_type
 * @property int $type
 * @property string|null $pages
 * @property int $view_intake_prices
 * @property int $allow_override_salesman
 *
 * @package App\Models
 */
class OldUser extends Model
{
    protected $connection = 'tandc_live';
	protected $table = 'users';
	public $timestamps = false;

	protected $casts = [
		'type' => 'int',
		'view_intake_prices' => 'int',
		'allow_override_salesman' => 'int'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'name',
		'email',
		'password',
		'user_type',
		'type',
		'pages',
		'view_intake_prices',
		'allow_override_salesman'
	];
}
