<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Pallet
 *
 * @property int $id
 * @property int|null $intake_id
 * @property string|null $comments
 * @property string|null $storage_location
 * @property int $grosspallet
 * @property float|null $gross_weight
 * @property float|null $pallet_tare
 * @property float|null $tare_per_carton
 * @property float|null $number_of_cartons
 * @property float|null $net_weight
 * @property string|null $user_id
 *
 * @package App\Models
 */
class Pallet extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'pallet';
	public $timestamps = true;

	protected $casts = [
		'intake_id' => 'int',
		'grosspallet' => 'int',
		'gross_weight' => 'float',
		'pallet_tare' => 'float',
		'tare_per_carton' => 'float',
		'number_of_cartons' => 'float',
		'net_weight' => 'float'
	];

	protected $fillable = [
		'intake_id',
		'comments',
		'storage_location',
		'grosspallet',
		'gross_weight',
		'pallet_tare',
		'tare_per_carton',
		'number_of_cartons',
		'net_weight',
		'user_id'
	];
    public function intake():BelongsTo {
        return $this->belongsTo(Intake::class,"id","intake_id");
    }
    public function weights():HasMany {
        return $this->hasMany(Weight::class,"pallet_id","id");
    }
}
