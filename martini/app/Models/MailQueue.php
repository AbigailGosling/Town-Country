<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MailQueue
 * 
 * @property int $customer_id
 *
 * @package App\Models
 */
class MailQueue extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'mail_queue';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'customer_id' => 'int'
	];

	protected $fillable = [
		'customer_id'
	];
}
