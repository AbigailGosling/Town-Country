<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SupplierReturn
 * 
 * @property int $id
 * @property int $user_id
 * @property int $supplier_id
 * @property int $pick_id
 * @property string|null $reference_number
 * @property string|null $comments
 * @property bool $deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class SupplierReturn extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'supplier_returns';

	protected $casts = [
		'user_id' => 'int',
		'supplier_id' => 'int',
		'pick_id' => 'int',
		'deleted' => 'bool'
	];

	protected $fillable = [
		'user_id',
		'supplier_id',
		'pick_id',
		'reference_number',
		'comments',
		'deleted'
	];
}
