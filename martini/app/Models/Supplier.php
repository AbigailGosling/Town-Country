<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Supplier
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $address_3
 * @property string|null $address_4
 * @property string|null $postcode
 * @property string|null $email
 * @property string|null $contact_number
 * @property string|null $contact_name
 * @property int|null $user_id
 * @property string|null $internal_number
 * @property bool $disabled
 *
 * @package App\Models
 */
class Supplier extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'supplier';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'disabled' => 'bool'
	];

	protected $fillable = [
		'name',
		'address_1',
		'address_2',
		'address_3',
		'address_4',
		'postcode',
		'email',
		'contact_number',
		'contact_name',
		'user_id',
		'internal_number',
		'disabled'
	];
}
