<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalPalletMovement extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
    protected $table = 'internal_pallet_movement';
    protected $fillable = [
        'pallet_id',
        'from_location_id',
        'to_location_id',
        'movement_initiated_by',
        'movement_processed_by',
        'site_to_site',
        'processed',
        'accepted',
    ];
    protected $casts = [
        'site_to_site' => 'boolean',
        'processed' => 'boolean',
        'accepted' => 'boolean',
    ];
    public $timestamps = true;

    public function pallet()
    {
        return $this->belongsTo(Pallet::class, 'pallet_id');
    }
    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }
    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }
    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'movement_initiated_by');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'movement_accepted_by');
    }
}
