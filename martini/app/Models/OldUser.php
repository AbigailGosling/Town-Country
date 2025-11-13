<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function customers():HasMany{
        return $this->hasMany(Customer::class,"default_salesman_id","id");
    }
    public function auth_user():BelongsTo{
        return $this->BelongsTo(User::class,"id","id");
    }
}
