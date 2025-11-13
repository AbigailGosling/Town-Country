<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationProduct extends Model
{
    protected $table = 'reservation_product';
    protected $connection = 'tandc_live';
    public $timestamps = false;

    protected $fillable = [
        'reservation_id',
        'product_id',
        'target_count',
        'price',
    ];

    protected $casts = [
        'reservation_id' => 'integer',
        'product_id' => 'integer',
        'target_count' => 'integer',
        'price' => 'decimal:3',
    ];
    public function reservation()
    {
        return $this->belongsTo(Reservation::class,"id","reservation_id");
    }

    public function product()
    {
        return $this->hasOne(Product::class,"id","product_id");
    }
}
