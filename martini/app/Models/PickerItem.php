<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PickerItem
 * 
 * @property int $id
 * @property int|null $pickersheet_id
 * @property int|null $product_id
 * @property string|null $price
 * @property string|null $price_type
 * @property string|null $status
 * @property string|null $comment
 * @property int $deleted
 * @property int|null $target_weight
 *
 * @package App\Models
 */
class PickerItem extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'pickerItems';
	public $timestamps = false;

	protected $casts = [
		'pickersheet_id' => 'int',
		'product_id' => 'int',
		'deleted' => 'int',
		'target_weight' => 'int'
	];

	protected $fillable = [
		'pickersheet_id',
		'product_id',
		'price',
		'price_type',
		'status',
		'comment',
		'deleted',
		'target_weight'
	];
}
