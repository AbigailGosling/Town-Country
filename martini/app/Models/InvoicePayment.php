<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvoicePayment
 *
 * @property int $id
 * @property int $invoice_id
 * @property string $payment_method
 * @property float $amount
 * @property string|null $meta_data
 * @property int $payment_recorded_by
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property bool $deleted
 *
 * @package App\Models
 */
class InvoicePayment extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'invoice_payments';
	public $timestamps = false;

	protected $casts = [
		'invoice_id' => 'int',
		'amount' => 'float',
		'payment_recorded_by' => 'int'
	];

	protected $fillable = [
		'invoice_id',
		'payment_method',
		'amount',
		'meta_data',
		'payment_recorded_by'
	];
}
