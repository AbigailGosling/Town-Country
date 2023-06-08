<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Weight
 * 
 * @property int $id
 * @property int|null $product_id
 * @property int|null $status_id
 * @property string|null $weight_gross
 * @property string|null $weight_tear
 * @property float|null $pallet_tare
 * @property float|null $tare_per_carton
 * @property float|null $number_of_cartons
 * @property string|null $original_gross
 * @property int $tampered
 * @property int|null $grosstare
 *
 * @package App\Models
 */
class Weight extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'weights';
	public $timestamps = false;

	protected $casts = [
		'product_id' => 'int',
		'status_id' => 'int',
		'pallet_tare' => 'float',
		'tare_per_carton' => 'float',
		'number_of_cartons' => 'float',
		'tampered' => 'int',
		'grosstare' => 'int'
	];

	protected $fillable = [
		'product_id',
		'status_id',
		'weight_gross',
		'weight_tear',
		'pallet_tare',
		'tare_per_carton',
		'number_of_cartons',
		'original_gross',
		'tampered',
		'grosstare'
	];
}
