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
 * @property string|null $postcode
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
		'postcode',
		'contact_number',
		'contact_name',
		'user_id',
		'internal_number',
		'disabled'
	];
}
