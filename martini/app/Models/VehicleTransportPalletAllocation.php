<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleTransportPalletAllocation extends Model
{
    protected $connection = 'tandc_live';
    protected $table = 'vehicle_transport_pallet_allocations';
    protected $fillable = [
        'vehicle_id',
        'load_sheet_id',
        'transport_pallet_id',
        'row',
        'column',
        'committed_by_user_id',
        'committed_by_name',
        'committed_at',
    ];

    protected $casts = [
        'vehicle_id' => 'integer',
        'load_sheet_id' => 'integer',
        'transport_pallet_id' => 'integer',
        'row' => 'integer',
        'column' => 'integer',
        'committed_by_user_id' => 'integer',
        'committed_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function loadSheet()
    {
        return $this->belongsTo(LoadSheet::class, 'load_sheet_id');
    }

    public function transportPallet()
    {
        return $this->belongsTo(TransportPallet::class);
    }
}
