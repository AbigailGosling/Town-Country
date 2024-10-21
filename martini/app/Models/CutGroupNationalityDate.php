<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * Class CutGroupNationalityDate
 *
 * @property int $id
 * @property int $nationality_id
 * @property int $cutgroup_id
 *
 * @property Nationality $nationality
 * @property CutGroup $cutgroup
 *
 * @property int $warning
 * @property int $dander
 *
 * @package App\Models
 */
class CutGroupNationalityDate extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'cutgroup_nationality_dates';
	public $incrementing = true;
	public $timestamps = false;

	protected $casts = [
		'nationality_id' => 'int',
		'cutgroup_id' => 'int',
		'warning' => 'int',
		'danger' => 'int',
	];

	protected $fillable = [
		'nationality_id',
		'cutgroup_id',
		'warning',
		'danger',
	];
	public function nationality(): BelongsTo
	{
		return $this->belongsTo(Nationality::class, 'nationality_id', 'id');
	}

	public function cutgroup(): BelongsTo
	{
		return $this->belongsTo(CutGroup::class, 'cutgroup_id', 'id');
	}
	public function species(): HasOneThrough
    {
        return $this->hasOneThrough(CutGroup::class, Species::class,"id","species_id","cutgroup_id");
    }
	public function getSpeciesID():int
	{
		return (int)$this->cutgroup()->get()->first()['species_id'];
	}
    static $STORED_RESULTS = array();
	static function lookupFromProductID(int $id):CutGroupNationalityDate|null
	{

        if (array_key_exists($id,static::$STORED_RESULTS)) return static::$STORED_RESULTS[$id];
		else
        {
            $result = static::lookupFromProduct(Product::find($id));
            $STORED_RESULTS[$id] = $result;
            return $result;
        }
	}
	static function lookupFromProduct(Product $product):CutGroupNationalityDate|null
	{
		/** @var Cut $cut */
		$cut = Cut::find($product->cut_id);
		$r = static::where(['cutgroup_id'=>$cut->cutgroup_id,'nationality_id'=>$product->nationality_id]);
		$re = $r->first();
		return $re;
	}
}
