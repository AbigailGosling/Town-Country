<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PickersheetDocument
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string|null $pickersheet_id
 * @property string|null $message
 * @property string|null $dfile
 * @property string|null $type
 *
 * @package App\Models
 */
class PickersheetDocument extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'pickersheet_documents';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'pickersheet_id',
		'message',
		'dfile',
		'type'
	];
}
