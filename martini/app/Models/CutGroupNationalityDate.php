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
    static $STORED_BY_PROD = array();
    static $STORED_BY_CUT_NAT=array();
    static $STORED_BY_CUTGROUP_NAT=array();
	static function lookupFromProductID(int $id):CutGroupNationalityDate|null
	{
        if (!array_key_exists($id,static::$STORED_BY_PROD))
        {
            /** @var Product $p */
            $p = Product::find($id);
            static::$STORED_BY_PROD[$id] = static::lookupFromCutNationalityIDs($p->cut_id,$p->nationality_id);
        }

        return static::$STORED_BY_PROD[$id];
	}
	static function lookupFromCutNationalityIDs(?int $cut_id,?int $nationality_id):CutGroupNationalityDate|null
	{
        if (!array_key_exists($cut_id."_".$nationality_id,static::$STORED_BY_CUT_NAT))
        {
            /** @var Cut $cut */
		    $cut = Cut::find($cut_id);
            static::lookupFromCutGroupNationalityIDs($cut->cutgroup_id,$nationality_id);
        }
		return static::$STORED_BY_CUT_NAT[$cut_id."_".$nationality_id];
	}
    static function lookupFromCutGroupNationalityIDs(?int $cutgroup_id,?int $nationality_id):CutGroupNationalityDate|null
    {
        if (!array_key_exists($cutgroup_id."_".$nationality_id,static::$STORED_BY_CUTGROUP_NAT))
        {
            static::$STORED_BY_CUTGROUP_NAT[$cutgroup_id."_".$nationality_id] = static::where(['cutgroup_id'=>$cutgroup_id,'nationality_id'=>$nationality_id])->first();
            /** @var Cut $cut */
            foreach (Cut::where('cutgroup_id',$cutgroup_id)->get() as $cut)
            {
                static::$STORED_BY_CUT_NAT[$cut->id."_".$nationality_id] = static::$STORED_BY_CUTGROUP_NAT[$cutgroup_id."_".$nationality_id];
            }
        }
		return static::$STORED_BY_CUTGROUP_NAT[$cutgroup_id."_".$nationality_id];
    }
}
