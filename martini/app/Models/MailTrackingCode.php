<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MailTrackingCode
 * 
 * @property int $id
 * @property string $value
 *
 * @package App\Models
 */
class MailTrackingCode extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'mail_tracking_codes';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'value'
	];
}
