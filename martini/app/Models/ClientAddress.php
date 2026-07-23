<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * App\Models\ClientAddress
 *
 * @property int $id
 * @property int $client_id
 * @property int $address_id
 * @property string|null $address_number
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $address_3
 * @property string|null $address_4
 * @property string|null $postcode
 * @property float|null $lat
 * @property float|null $lon
 * @property int|null $site_id
 * @property string|null $client_type
 * @property bool|null $geocoding_tried
 * @property bool|null $collection
 * @property string|null $allowed_vehicle_types
 * @property bool|null $require_tail_lift
 * @property bool|null $require_fork_lift
 * @property \Illuminate\Support\Carbon|null $opening_time
 * @property \Illuminate\Support\Carbon|null $closing_time
 * @property bool|null $open_bank_holiday_mondays
 * @property bool|null $open_bank_holiday_fridays
 */
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
        'allowed_vehicle_types',
        'require_tail_lift',
        'require_fork_lift',
        'opening_time',
        'closing_time',
        'open_bank_holiday_mondays',
        'open_bank_holiday_fridays',
    ];

    protected $casts = [
        'client_id' => 'int',
        'address_id' => 'int',
        'lat' => 'float',
        'lon' => 'float',
        'site_id' => 'int',
        'geocoding_tried' => 'boolean',
        'collection' => 'boolean',
        'allowed_vehicle_types' => 'string',
        'require_tail_lift' => 'boolean',
        'require_fork_lift' => 'boolean',
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'open_bank_holiday_mondays' => 'boolean',
        'open_bank_holiday_fridays' => 'boolean',
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
