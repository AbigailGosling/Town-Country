<?php

namespace App\Models;

use App\Helpers\FuncHelper;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    // Enforce the database connection to 'tandc_live'
    protected $connection = 'tandc_live';
    protected $table = 'vehicle';
    public $timestamps = false;

    protected $fillable = [
        'reg',
        'vehicle_type_id',
        'make',
        'model',
        'grossWeight',
        'payload',
        'site_id',
        'driver',
        'max_pallet_rows',
        'barracuda_id',
        'lat',
        'lon',
        'disabled',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }
    public function outgoingPalletAllocations()
    {
        return $this->hasMany(VehicleOutgoingPalletAllocation::class, 'vehicle_id');
    }
    public function planningPayloadForVehicle(): ?int
    {
        $payload = str_replace("*", "", str_replace('t', '', strtolower($this->payload)));
        return is_numeric($payload) ? (int) FuncHelper::floorDec(((float)$payload)*1000,0) : null;
    }
    public function planningCapacityForVehicle(int $planningPalletColumns): int
    {
        $maxRows = $this->max_pallet_rows ?? 5;
        return max($planningPalletColumns, $maxRows * $planningPalletColumns);
    }
}
