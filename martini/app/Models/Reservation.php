<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $user_id
 * @property int         $customer_id
 * @property int         $address_id
 * @property string|null $picksheet_note
 * @property string|null $order_reference_number
 * @property bool        $processed
 * @property \Illuminate\Support\Carbon $eta
 * @property string|null $transaction_id
 * @property string|null $goods_out_notes
 */
class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservation';

    protected $connection = 'tandc_live';

    protected $fillable = [
        'user_id',
        'customer_id',
        'address_id',
        'picksheet_note',
        'order_reference_number',
        'processed',
        'pickersheet_id',
        'eta',
        'transaction_id',
        'goods_out_notes',
    ];

    protected $casts = [
        'user_id'                => 'integer',
        'address_id'             => 'integer',
        'customer_id'            => 'integer',
        'picksheet_note'         => 'string',
        'order_reference_number' => 'string',
        'processed'              => 'boolean',
        'eta'                    => 'datetime:Y-m-d',
        'goods_out_notes'        => 'string',
        'pickersheet_id'         => 'integer',
    ];
    public function reservation_product():HasMany
    {
        return $this->hasMany(ReservationProduct::class, 'reservation_id');
    }
}
