<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Weight
 *
 * @property int $id
 * @property int|null $product_id
 * @property int|null $status_id
 * @property float|null $weight_gross
 * @property float|null $weight_tear
 * @property float|null $pallet_tare
 * @property float|null $tare_per_carton
 * @property float|null $number_of_cartons
 * @property string|null $original_gross
 * @property int $tampered
 * @property int|null $grosstare
 *
 * @package App\Models
 */
class Weight extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'weights';
	public $timestamps = true;

	protected $casts = [
		'product_id' => 'int',
		'status_id' => 'int',
		'weight_gross' => 'float',
		'weight_tear' => 'float',
		'pallet_tare' => 'float',
		'tare_per_carton' => 'float',
		'number_of_cartons' => 'float',
		'tampered' => 'int',
		'grosstare' => 'int'
	];

	protected $fillable = [
		'product_id',
		'status_id',
		'weight_gross',
		'weight_tear',
		'pallet_tare',
		'tare_per_carton',
		'number_of_cartons',
		'original_gross',
		'tampered',
		'grosstare'
	];
    public function pallet():BelongsTo{
        return $this->belongsTo(Pallet::class,"pallet_id","id");
    }
    public function product():BelongsTo{
        return $this->belongsTo(Product::class,"product_id","id");
    }
    public function getNetWeight(){
        if($this->weight_tear == $this->weight_gross){
            (double)$netWeight = (double)$this->weight_gross;
        }else{
            (double)$netWeight = (double)$this->weight_gross - (double)$this->weight_tear;
        }
        return $netWeight;
    }
}
