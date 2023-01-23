<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Temperature
 * 
 * @property int $id
 * @property string|null $temperature
 *
 * @package App\Models
 */
class Temperature extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'temperature';
	public $timestamps = false;

	protected $fillable = [
		'temperature'
	];
}
