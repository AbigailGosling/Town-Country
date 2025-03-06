<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MailTracking
 *
 * @property int $id
 * @property int $customer_id
 * @property int|null $document_id
 * @property string $addressee
 * @property string $message_id
 * @property string $type
 * @property string $status
 * @property int $secondary_code
 * @property string|null $attachments
 * @property Carbon $date_sent
 * @property Carbon|null $last_update
 *
 * @package App\Models
 */
class MailTracking extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'mail_tracking';
	public $timestamps = false;

	protected $casts = [
		'customer_id' => 'int',
		'document_id' => 'int',
		'secondary_code' => 'int'
	];

	protected $dates = [
		'date_sent',
		'last_update'
	];

	protected $fillable = [
		'customer_id',
		'document_id',
		'addressee',
		'message_id',
		'type',
		'status',
		'secondary_code',
		'attachments',
		'date_sent',
		'last_update'
	];
}
