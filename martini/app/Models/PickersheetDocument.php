<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class PickersheetDocument
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $pickersheet_id
 * @property string|null $message
 * @property string|null $dfile
 * @property string|null $type
 * @property int|null $file_id
 * @property string|null $driver_name
 * @property Carbon|null $device_timestamp
 *
 * @package App\Models
 */
class PickersheetDocument extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'pickersheet_documents';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'device_timestamp' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'pickersheet_id',
		'message',
		'dfile',
		'type',
		'file_id',
		'driver_name',
		'device_timestamp'
	];
}
