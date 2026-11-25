<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Class CollectionReturnMetadata
 *
 * @package App\Models
 * @property int $transport_pallet_id
 * @property int $transaction_id
 * @property string $type_selector
 * @property bool $is_supplier
 * @property int $client_id
 * @property int $invoice_id
 * @property int $address_id
 * @property \Carbon\Carbon $estimated_delivery_date
 * @property int $submitted_by
 * @property string $picksheet_note
 * @property string $order_reference_number
 * @property int $approved_by
 * @property \Carbon\Carbon $approved_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CollectionReturnMetadata extends Model
{
    protected $connection = 'tandc_live';

    protected $table = 'collection_return_metadata';

    public $timestamps = true;

    protected $fillable = [
        'transport_pallet_id',
        'transaction_id',
        'type_selector',
        'is_supplier',
        'client_id',
        'invoice_id',
        'address_id',
        'estimated_delivery_date',
        'submitted_by',
        'picksheet_note',
        'order_reference_number',
        'approved_by',
        'approved_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'transport_pallet_id' => 'integer',
        'is_supplier' => 'boolean',
        'client_id' => 'integer',
        'invoice_id' => 'integer',
        'address_id' => 'integer',
        'submitted_by' => 'integer',
        'approved_by' => 'integer',
        'approved_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'estimated_delivery_date' => 'datetime',
    ];

    public function transportPallet(): BelongsTo
    {
        return $this->belongsTo(TransportPallet::class, 'transport_pallet_id');
    }
}
