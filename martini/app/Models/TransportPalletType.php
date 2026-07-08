<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportPalletType extends Model
{
    protected $connection = 'tandc_live';
    protected $table = 'tranport_pallet_types';

    protected $fillable = [
        'name',
        'max_weight',
    ];

    protected $casts = [
        'max_weight' => 'decimal:4',
    ];

    public function transportPallets()
    {
        return $this->hasMany(TransportPallet::class, 'transport_pallet_type_id');
    }
}
