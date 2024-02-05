<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Intake
 * 
 * @property int $id
 * @property int $returned
 * @property string|null $supplier_id
 * @property string|null $purchase_id
 * @property string|null $vehicle_reg
 * @property string|null $vehicle_temp
 * @property string|null $delivery_note_number
 * @property string|null $user_id
 * @property string|null $product_temperature
 * @property string|null $vehicle_temperature
 * @property string|null $date_received
 * @property string|null $security_id
 * @property string|null $notes
 * @property Carbon|null $date_paid
 *
 * @package App\Models
 */
class Intake extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'intake';
	public $timestamps = true;

	protected $casts = [
		'returned' => 'int'
	];

	protected $dates = [
		'date_paid'
	];

	protected $fillable = [
		'returned',
		'supplier_id',
		'purchase_id',
		'vehicle_reg',
		'vehicle_temp',
		'delivery_note_number',
		'user_id',
		'product_temperature',
		'vehicle_temperature',
		'date_received',
		'security_id',
		'notes',
		'date_paid'
	];
}
