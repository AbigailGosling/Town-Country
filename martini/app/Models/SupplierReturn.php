<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierReturn extends Model
{
    use HasFactory;

    protected $table = 'supplier_returns';

    protected $fillable = [
        'user_id',
        'supplier_id',
        'pick_id',
        'reference_number',
        'comments',
        'deleted'
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];
}
