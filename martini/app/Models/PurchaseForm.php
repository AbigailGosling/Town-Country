<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PurchaseForm
 * 
 * @property int $id
 * @property string|null $species
 * @property string|null $cut
 * @property string|null $units
 * @property string|null $price
 * @property string|null $supplier_id
 * @property string|null $date_purchased
 * @property string|null $date_due
 * @property string|null $purchased_by
 * @property string|null $purchase_comments
 * @property string|null $dfile
 * @property string|null $booking_ref_number
 * @property string|null $transportation
 * @property string|null $haulier
 * @property int $direct_drop
 * @property int|null $temperature_id
 *
 * @package App\Models
 */
class PurchaseForm extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'purchase_form';
	public $timestamps = false;

	protected $casts = [
		'direct_drop' => 'int',
		'temperature_id' => 'int'
	];

	protected $fillable = [
		'species',
		'cut',
		'units',
		'price',
		'supplier_id',
		'date_purchased',
		'date_due',
		'purchased_by',
		'purchase_comments',
		'dfile',
		'booking_ref_number',
		'transportation',
		'haulier',
		'direct_drop',
		'temperature_id'
	];
}
