<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAddress extends Model
{
    use HasFactory;

    protected $connection = 'tandc_live';

    protected $table = 'client_addresses';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'address_id',
        'address_number',
        'address_1',
        'address_2',
        'address_3',
        'address_4',
        'postcode',
        'lat',
        'lon',
        'site_id',
        'client_type',
        'geocoding_tried',
        'collection',
    ];

    protected $casts = [
        'client_id' => 'int',
        'address_id' => 'int',
        'lat' => 'float',
        'lon' => 'float',
        'site_id' => 'int',
        'geocoding_tried' => 'boolean',
        'collection' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        if ($this->client_type === ClientType::CUSTOMER->value) {
            return $this->belongsTo(Customer::class, 'client_id', 'id');
        }
        else if ($this->client_type === ClientType::SUPPLIER->value) {
            return $this->belongsTo(Supplier::class, 'client_id', 'id');
        }
        else {
            throw new \Exception("Invalid client type: " . $this->client_type);
        }
    }
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }
}
enum ClientType: string
{
    case CUSTOMER = 'customer';
    case SUPPLIER = 'supplier';
}
