<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $connection = 'tandc_live';
    protected $table = 'vehicle_type';
    protected $fillable = [
        'name',
    ];
}
