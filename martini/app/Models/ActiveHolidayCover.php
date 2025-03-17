<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ActiveHolidayCover
 * 
 * @property int $id
 * @property int $absent_id
 * @property int $cover_id
 *
 * @package App\Models
 */
class ActiveHolidayCover extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'active_holiday_cover';
	public $timestamps = false;

	protected $casts = [
		'absent_id' => 'int',
		'cover_id' => 'int'
	];

	protected $fillable = [
		'absent_id',
		'cover_id'
	];
}
