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
        'committed_by_user_id',
        'committed_by_name',
        'committed_at',
    ];

    protected $casts = [
        'vehicle_id' => 'integer',
        'outgoing_pallet_id' => 'integer',
        'row' => 'integer',
        'column' => 'integer',
        'committed_by_user_id' => 'integer',
        'committed_at' => 'datetime',
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
