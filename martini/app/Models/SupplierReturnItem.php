<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierReturnItem extends Model
{
    use HasFactory;

    protected $table = 'supplier_return_items';

    protected $fillable = [
        'supplier_return_id',
        'product_id',
        'cases',
        'deleted',
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];
}
