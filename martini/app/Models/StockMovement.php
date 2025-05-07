<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StockMovement
 *
 * @property int $id
 * @property int $origin
 * @property int $destination
 * @property int $days
 *
 * @package App\Models
 */
class StockMovement extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'stock_movements';
    public $timestamps = true;
    protected $fillable = [
		'origin',
        'destination',
        'days',
	];
    function isMirrored():bool
    {
        if (!isset($this->destination) || !$this->destination===null) return false;
        return static::where([["origin",$this->destination],["destination",$this->origin],["days",$this->days]])->exists();
        //return !$i;
    }
    function getOrigin():Site
    {
        return Site::find($this->origin);
    }
    function getDestination():Site
    {
        return Site::find($this->destination);
    }
}
