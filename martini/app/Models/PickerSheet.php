<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PickerSheet
 * 
 * @property int $id
 * @property string|null $picker_id
 * @property string|null $user_from_id
 * @property int|null $customer_id
 * @property string|null $estimated_delivery_date
 * @property string|null $comments
 * @property string|null $completed
 * @property string $completed_frozen
 * @property string $completed_fresh
 * @property Carbon|null $date
 * @property Carbon|null $date_completed
 * @property string|null $orderReferenceNumber
 * @property int|null $deliverynote_printed
 * @property int $invoice_printed
 * @property int $sent
 * @property string $addressid
 * @property string|null $completedby_userid
 * @property string|null $invoicesent
 * @property int $deleted
 * @property int|null $deleted_by_user_id
 * @property string|null $picksheet_note
 * @property bool $admin_approved
 * @property string|null $transaction_id
 * @property bool $isSupplemental
 *
 * @package App\Models
 */
class PickerSheet extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'pickerSheets';
	public $timestamps = false;

	protected $casts = [
		'customer_id' => 'int',
		'deliverynote_printed' => 'int',
		'invoice_printed' => 'int',
		'sent' => 'int',
		'deleted' => 'int',
		'deleted_by_user_id' => 'int',
		'admin_approved' => 'bool',
		'isSupplemental' => 'bool'
	];

	protected $dates = [
		'date',
		'date_completed'
	];

	protected $fillable = [
		'picker_id',
		'user_from_id',
		'customer_id',
		'estimated_delivery_date',
		'comments',
		'completed',
		'completed_frozen',
		'completed_fresh',
		'date',
		'date_completed',
		'orderReferenceNumber',
		'deliverynote_printed',
		'invoice_printed',
		'sent',
		'addressid',
		'completedby_userid',
		'invoicesent',
		'deleted',
		'deleted_by_user_id',
		'picksheet_note',
		'admin_approved',
		'transaction_id',
		'isSupplemental'
	];
}
