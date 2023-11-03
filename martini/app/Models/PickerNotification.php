<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PickerNotification
 * 
 * @property int $id
 * @property int $user_id
 * @property int $pickersheet_id
 * @property string $message
 * @property bool $locked
 * @property bool $lock_release
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class PickerNotification extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'pickerNotifications';

	protected $casts = [
		'user_id' => 'int',
		'pickersheet_id' => 'int',
		'locked' => 'bool',
		'lock_release' => 'bool'
	];

	protected $fillable = [
		'user_id',
		'pickersheet_id',
		'message',
		'locked',
		'lock_release'
	];
}
