<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadSheet extends Model
{
    use HasFactory;

    protected $connection = 'tandc_live';

    protected $table = 'load_sheets';

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'date',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'vehicle_id' => 'integer',
        'date' => 'date',
    ];

    public function allocations()
    {
        return $this->hasMany(VehicleTransportPalletAllocation::class, 'load_sheet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
