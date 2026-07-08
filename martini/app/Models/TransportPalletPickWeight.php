<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportPalletPickWeight extends Model
{
    protected $connection = 'tandc_live';

    protected $table = 'transport_pallet_pickWeights';

    protected $fillable = [
        'transport_pallet_id',
        'pickWeightOut_id',
    ];

    protected $casts = [
        'transport_pallet_id' => 'integer',
        'pickWeightOut_id' => 'integer',
    ];

    public function transportPallet(): BelongsTo
    {
        return $this->belongsTo(TransportPallet::class, 'transport_pallet_id');
    }

    public function pickWeightOut(): BelongsTo
    {
        return $this->belongsTo(PickWeightOut::class, 'pickWeightOut_id');
    }
}
