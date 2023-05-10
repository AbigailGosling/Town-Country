<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Box
 * 
 * @property int $id
 * @property int|null $product_id
 * @property string|null $status_id
 * @property string|null $weight
 * @property string|null $unit
 *
 * @package App\Models
 */
class Box extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'boxes';
	public $timestamps = false;

	protected $casts = [
		'product_id' => 'int'
	];

	protected $fillable = [
		'product_id',
		'status_id',
		'weight',
		'unit'
	];
}
