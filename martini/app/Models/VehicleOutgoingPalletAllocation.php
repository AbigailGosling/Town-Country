<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleOutgoingPalletAllocation extends Model
{
    protected $connection = 'tandc_live';
    protected $table = 'vehicle_outgoing_pallet_allocations';
    protected $fillable = [
        'vehicle_id',
        'outgoing_pallet_id',
        'row',
        'column',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function outgoingPallet()
    {
        return $this->belongsTo(OutgoingPallet::class);
    }
}
