<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CreditNoteItem
 *
 * @property int $id
 * @property int|null $payment_id
 * @property int|null $product_id
 * @property int|null $quantity
 * @property string|null $price
 * @property string|null $description
 * @property bool|null $deleted
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @package App\Models
 */
class CreditNoteItem extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'credit_note_items';
	public $timestamps = false;

	protected $casts = [
		'payment_id' => 'int',
		'product_id' => 'int',
		'quantity' => 'int'
	];

	protected $fillable = [
		'payment_id',
		'product_id',
		'quantity',
		'price',
		'description'
	];
}
