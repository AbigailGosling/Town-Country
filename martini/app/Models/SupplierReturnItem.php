<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SupplierReturnItem
 * 
 * @property int $id
 * @property int $supplier_return_id
 * @property int $product_id
 * @property int $cases
 * @property bool $deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class SupplierReturnItem extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'supplier_return_items';

	protected $casts = [
		'supplier_return_id' => 'int',
		'product_id' => 'int',
		'cases' => 'int',
		'deleted' => 'bool'
	];

	protected $fillable = [
		'supplier_return_id',
		'product_id',
		'cases',
		'deleted'
	];
}
