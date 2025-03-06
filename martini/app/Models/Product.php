<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Product
 *
 * @property int $id
 * @property int|null $pallet_id
 * @property int|null $cut_id
 * @property string|null $brand_id
 * @property string|null $nationality_id
 * @property string|null $cooling_id
 * @property string $status
 * @property string|null $range_from
 * @property string|null $range_to
 * @property string|null $range_extension
 * @property string|null $ubbb
 * @property string|null $unit
 * @property string|null $comments
 * @property string|null $best_by
 * @property string|null $pricetype
 * @property string|null $cost
 * @property string|null $price
 * @property string|null $box_id
 * @property string|null $weightnote
 * @property string|null $product_temp
 * @property string|null $original_intake_id
 * @property string|null $original_pallet_id
 * @property string|null $note_units
 * @property string|null $note_weight
 * @property string|null $akg
 * @property int|null $quantity
 *
 * @property Cut|null $cut
 *
 * @package App\Models
 */
class Product extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'product';
	public $timestamps = true;

	protected $casts = [
		'pallet_id' => 'int',
		'cut_id' => 'int',
		'quantity' => 'int',
		'health_id' => 'int'
	];

	protected $fillable = [
		'pallet_id',
		'cut_id',
		'brand_id',
		'nationality_id',
		'cooling_id',
		'status',
		'range_from',
		'range_to',
		'range_extension',
		'ubbb',
		'unit',
		'comments',
		'best_by',
		'pricetype',
		'cost',
		'price',
		'box_id',
		'weightnote',
		'product_temp',
		'original_intake_id',
		'original_pallet_id',
		'note_units',
		'note_weight',
		'akg',
		'quantity',
		'health_id',
		'kill_date'
	];
	public function cut():BelongsTo
	{
		return $this->belongsTo(Cut::class,"cut_id","id");
	}
}
