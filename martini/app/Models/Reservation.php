<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'eta',
        'transaction_id'
    ];

    protected $casts = [
        'user_id'                => 'integer',
        'address_id'             => 'integer',
        'customer_id'            => 'integer',
        'picksheet_note'         => 'string',
        'order_reference_number' => 'string',
        'processed'              => 'boolean',
        'eta'                    => 'datetime:Y-m-d',
    ];
    public function reservation_product():HasMany
    {
        return $this->hasMany(ReservationProduct::class, 'reservation_id');
    }
}
