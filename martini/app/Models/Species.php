<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Species
 * 
 * @property int $id
 * @property string|null $name
 *
 * @package App\Models
 */
class Species extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'species';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];
}
