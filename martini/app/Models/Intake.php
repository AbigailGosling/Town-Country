<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class Intake
 *
 * @property int $id
 * @property int $returned
 * @property string|null $supplier_id
 * @property string|null $purchase_id
 * @property string|null $vehicle_reg
 * @property string|null $vehicle_temp
 * @property string|null $delivery_note_number
 * @property string|null $user_id
 * @property string|null $product_temperature
 * @property string|null $vehicle_temperature
 * @property Carbon|null $date_received
 * @property string|null $security_id
 * @property string|null $notes
 * @property Carbon|null $date_paid
 * @property bool $deleted
 * @property bool $approved
 * @property int|null $approved_by
 * @property Carbon|null $approved_date
 * @property string|null $import_num
 * @property int $health_id
 * @property string|null $internal_num
 * @property string|null $packaging_notes
 *
 * @package App\Models
 */
class Intake extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'intake';
	public $timestamps = true;

	protected $casts = [
		'returned' => 'int',
		'deleted' => 'bool',
		'approved' => 'bool',
		'approved_by' => 'int',
		'approved_date' => 'datetime',
        'date_received' => 'datetime:Y-m-d H:n:s',
        'approving_start' => 'datetime:Y-m-d H:n:s',
		'health_id' => 'int'
	];
    protected $dates = [
		'date_paid'
	];

	protected $fillable = [
		'returned',
		'supplier_id',
		'purchase_id',
		'vehicle_reg',
		'vehicle_temp',
		'delivery_note_number',
		'user_id',
		'product_temperature',
		'vehicle_temperature',
		'date_received',
		'security_id',
		'notes',
		'date_paid',
		'deleted',
		'approved',
        'approving_start',
		'approved_by',
		'approved_date',
		'import_num',
		'health_id',
		'internal_num',
		'packaging_notes',
        'container_id'
	];
    public function pallets():HasMany{
        return $this->hasMany(Pallet::class,"intake_id","id");
    }
    public function products():HasManyThrough{
        return $this->hasManyThrough(
            Product::class, Pallet::class,
            'intake_id', 'pallet_id', 'id'
        );
    }
}
