<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Nationality
 * 
 * @property int $id
 * @property string|null $name
 *
 * @package App\Models
 */
class Nationality extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'nationality';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];
}
