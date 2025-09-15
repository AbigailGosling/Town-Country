<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationProduct extends Model
{
    // Explicitly define the table name since it's not plural
    protected $table = 'reservation_product';

    // If you want timestamps (created_at, updated_at) disable them since they're not in your schema
    public $timestamps = false;

    // Fillable fields for mass assignment
    protected $fillable = [
        'reservation_id',
        'product_id',
        'target_count',
        'price',
    ];

    // Casting fields
    protected $casts = [
        'reservation_id' => 'integer',
        'product_id' => 'integer',
        'target_count' => 'integer',
        'price' => 'decimal:3',
    ];

    /**
     * Example relationships
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class,"id","reservation_id");
    }

    public function product()
    {
        return $this->hasOne(Product::class,"id","product_id");
    }
}
