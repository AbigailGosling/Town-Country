<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Security
 * 
 * @property int $id
 * @property string|null $name
 *
 * @package App\Models
 */
class Security extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'security';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];
}
