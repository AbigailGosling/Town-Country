<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Cut
 * 
 * @property int $id
 * @property string|null $species_id
 * @property string|null $name
 * @property string|null $cutgroup_id
 *
 * @package App\Models
 */
class Cut extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'cuts';
	public $timestamps = false;

	protected $fillable = [
		'species_id',
		'name',
		'cutgroup_id'
	];
}
