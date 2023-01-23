<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FinanceReportHistory
 * 
 * @property int $id
 * @property int $user_id
 * @property Carbon $created
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property int $start_invoice_id
 * @property int $end_invoice_id
 * @property int $aborted_id
 * @property float $sales
 * @property float $payments
 * @property int $previous_id
 *
 * @package App\Models
 */
class FinanceReportHistory extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'finance_report_history';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'start_invoice_id' => 'int',
		'end_invoice_id' => 'int',
		'aborted_id' => 'int',
		'sales' => 'float',
		'payments' => 'float',
		'previous_id' => 'int'
	];

	protected $dates = [
		'created',
		'start_date',
		'end_date'
	];

	protected $fillable = [
		'user_id',
		'created',
		'start_date',
		'end_date',
		'start_invoice_id',
		'end_invoice_id',
		'aborted_id',
		'sales',
		'payments',
		'previous_id'
	];
}
