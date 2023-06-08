<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomersOld
 * 
 * @property int $id
 * @property string|null $businessname
 * @property string|null $name
 * @property string|null $contactnumber
 * @property string|null $deliverynumber
 * @property string|null $deliveryaddress
 * @property string|null $billingaddress
 * @property string|null $postcode
 * @property int $pricedefault
 *
 * @package App\Models
 */
class CustomersOld extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'customers_old';
	public $timestamps = false;

	protected $casts = [
		'pricedefault' => 'int'
	];

	protected $fillable = [
		'businessname',
		'name',
		'contactnumber',
		'deliverynumber',
		'deliveryaddress',
		'billingaddress',
		'postcode',
		'pricedefault'
	];
}
