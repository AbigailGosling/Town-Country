<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PalletsOut
 * 
 * @property int $id
 * @property int|null $pickersheet_id
 * @property string|null $weight_ids
 * @property string|null $stringName
 * @property string|null $picker_ids
 *
 * @package App\Models
 */
class PalletsOut extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'palletsOut';
	public $timestamps = false;

	protected $casts = [
		'pickersheet_id' => 'int'
	];

	protected $fillable = [
		'pickersheet_id',
		'weight_ids',
		'stringName',
		'picker_ids'
	];
}
