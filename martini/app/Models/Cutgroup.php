<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Cutgroup
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $species_id
 *
 * @package App\Models
 */
class Cutgroup extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'cutgroups';
	public $timestamps = false;

	protected $fillable = [
		'name',
		'species_id'
	];
}
