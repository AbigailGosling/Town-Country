<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SystemSetting
 * 
 * @property int $id
 * @property string $key_name
 * @property string $key_value
 *
 * @package App\Models
 */
class SystemSetting extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'system_settings';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'key_name',
		'key_value'
	];
}
