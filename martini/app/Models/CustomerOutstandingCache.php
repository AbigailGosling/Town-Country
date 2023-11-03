<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomerOutstandingCache
 * 
 * @property int $customer_id
 * @property int $pickersheet_id
 * @property int $invoice_payment_id
 * @property int $oldest_unpaid_id
 * @property float $outstanding
 * @property string|null $pickersheet_sha2
 * @property string|null $payment_sha2
 *
 * @package App\Models
 */
class CustomerOutstandingCache extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'customer_outstanding_cache';
	protected $primaryKey = 'customer_id';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'customer_id' => 'int',
		'pickersheet_id' => 'int',
		'invoice_payment_id' => 'int',
		'oldest_unpaid_id' => 'int',
		'outstanding' => 'float'
	];

	protected $fillable = [
		'pickersheet_id',
		'invoice_payment_id',
		'oldest_unpaid_id',
		'outstanding',
		'pickersheet_sha2',
		'payment_sha2'
	];
}
