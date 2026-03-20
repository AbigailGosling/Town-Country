<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutgoingPalletPickWeight extends Model
{
    protected $connection = 'tandc_live';

    protected $table = 'outgoing_pallet_pickWeights';

    protected $fillable = [
        'outgoing_pallet_id',
        'pickWeightOut_id',
    ];

    protected $casts = [
        'outgoing_pallet_id' => 'integer',
        'pickWeightOut_id' => 'integer',
    ];

    public function outgoingPallet(): BelongsTo
    {
        return $this->belongsTo(OutgoingPallet::class, 'outgoing_pallet_id');
    }

    public function pickWeightOut(): BelongsTo
    {
        return $this->belongsTo(PickWeightOut::class, 'pickWeightOut_id');
    }
}
