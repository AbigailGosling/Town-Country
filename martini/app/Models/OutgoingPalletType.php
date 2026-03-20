<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutgoingPalletType extends Model
{
    protected $connection = 'tandc_live';
    protected $table = 'outgoing_pallet_types';

    protected $fillable = [
        'name',
        'max_weight',
    ];

    protected $casts = [
        'max_weight' => 'decimal:4',
    ];

    public function outgoingPallets()
    {
        return $this->hasMany(OutgoingPallet::class, 'outgoing_pallet_type_id');
    }
}
