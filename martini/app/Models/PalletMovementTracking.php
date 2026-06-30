<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PalletMovementTracking extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
    protected $table = 'pallet_movement_tracking';
    public $timestamps = true;
    protected $fillable = [
        'pallet_id',
        'from_location',
        'to_location',
        'created_by',
    ];
    protected $casts = [
        'pallet_id' => 'int',
        'from_location' => 'int',
        'to_location' => 'int',
        'created_by' => 'int',
    ];
    public function pallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class, 'pallet_id', 'id');
    }
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location', 'id');
    }
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location', 'id');
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public static function moveStock(int $palletId, int $toLocationId): self|null
    {
        $pallet = Pallet::find($palletId);
        if ($pallet->storage_location == $toLocationId) return null;
        $l = self::create([
            'pallet_id' => $palletId,
            'from_location' => ($pallet->storage_location !== null && $pallet->storage_location !== "") ? $pallet->storage_location : -1,
            'to_location' => $toLocationId,
            'created_by' => auth()->id() ?? -1,
        ]);
        $pallet->storage_location = $toLocationId;
        $pallet->save();
        return $l;
    }
}
