<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Product
 *
 * @property int $id
 * @property int|null $pallet_id
 * @property int|null $cut_id
 * @property string|null $brand_id
 * @property string|null $nationality_id
 * @property string|null $cooling_id
 * @property string $status
 * @property string|null $range_from
 * @property string|null $range_to
 * @property string|null $range_extension
 * @property string|null $ubbb
 * @property string|null $unit
 * @property string|null $comments
 * @property string|null $best_by
 * @property string|null $pricetype
 * @property string|null $cost
 * @property string|null $price
 * @property string|null $rrp1
 * @property string|null $rrp2
 * @property string|null $rrp3
 * @property string|null $box_id
 * @property string|null $weightnote
 * @property string|null $product_temp
 * @property string|null $original_intake_id
 * @property string|null $original_pallet_id
 * @property string|null $note_units
 * @property string|null $note_weight
 * @property string|null $akg
 * @property string|null $old_akg
 * @property int|null $health_id
 * @property string|null $kill_date
 * @property int|null $quantity
 *
 * @property Cut|null $cut
 *
 * @package App\Models
 */
class Product extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'product';
	public $timestamps = true;

	protected $casts = [
		'pallet_id' => 'int',
		'cut_id' => 'int',
		'quantity' => 'int',
		'health_id' => 'int'
	];

	protected $fillable = [
		'pallet_id',
		'cut_id',
		'brand_id',
		'nationality_id',
		'cooling_id',
		'status',
		'range_from',
		'range_to',
		'range_extension',
		'ubbb',
		'unit',
		'comments',
		'best_by',
		'pricetype',
		'cost',
		'price',
		'box_id',
		'weightnote',
		'product_temp',
		'original_intake_id',
		'original_pallet_id',
		'note_units',
		'note_weight',
		'akg',
		'quantity',
		'health_id',
		'kill_date',
        'old_akg',
        'rrp1',
        'rrp2'
	];
	public function cut():BelongsTo
	{
		return $this->belongsTo(Cut::class,"cut_id","id");
	}
    private Cut $_cut;
    public function getCut():Cut|null
    {
        return $this->_cut ??= Cut::find($this->cut_id);
    }

    public function pallet():BelongsTo
    {
        return $this->belongsTo(Pallet::class,"pallet_id","id");
    }
    private Pallet $_pallet;
    public function getPallet():Pallet|null
    {
        return $this->_pallet ??= Pallet::find($this->pallet_id);
    }
    public function weights():HasMany
    {
        return $this->hasMany(Weight::class,"product_id","id");
    }
    public function getWeights():Collection
    {
        return Weight::where("product_id",$this->id)->get();
    }
    public function brand():BelongsTo
	{
		return $this->belongsTo(Brand::class,"brand_id","id");
	}
    private Brand $_brand;
    public function getBrand():Brand|null
    {
        return $this->_brand ??= Brand::find($this->brand_id);
    }
    public function nationality():BelongsTo
    {
        return $this->belongsTo(Nationality::class,"nationality_id","id");
    }
    private Nationality $_nationality;
    public function getNationality():Nationality|null
    {
        return $this->_nationality ??= Nationality::find($this->nationality_id);
    }
    public function temperature():BelongsTo
    {
        return $this->belongsTo(Temperature::class,"cooling_id","id");
    }
    private Temperature $_temperature;
    public function getTemperature():Temperature|null
    {
        return $this->_temperature ??= Temperature::find($this->cooling_id);
    }
    public function healthMark():BelongsTo
    {
        return $this->belongsTo(HealthMark::class,"health_id","id");
    }
    private HealthMark $_health;
    public function getHealthMark():HealthMark|null
    {
        return $this->_health ??= HealthMark::find($this->health_id);
    }
}
