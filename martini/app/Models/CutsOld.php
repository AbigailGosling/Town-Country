<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CutsOld
 * 
 * @property int $id
 * @property string|null $species_id
 * @property string|null $name
 *
 * @package App\Models
 */
class CutsOld extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'cuts_old';
	public $timestamps = false;

	protected $fillable = [
		'species_id',
		'name'
	];
}
