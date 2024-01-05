<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class CutGroup
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $species_id
 *
 * @package App\Models
 */
class CutGroup extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'cutgroups';
	public $timestamps = false;

	protected $fillable = [
		'name',
		'species_id'
	];
	public function cuts(): HasMany
    {
        return $this->hasMany(Cut::class, 'cutgroup_id', 'id');
    }
	public function species(): HasOne
    {
        return $this->hasOne(Species::class,'id','species_id');
    }
	public static function generateHTMLList(int $selected = null,int $species_id = null):string{
        $output = "";
		$dataSet = ($species_id == null) ? self::all() : self::where("species_id",$species_id)->get();
        foreach($dataSet as $site){
			if ($site->name == "")continue;
            $selectFlag = ($selected != null && $selected == $site->id) ? " selected" : "";
			$output = $output . "<option value='".$site->id."'".$selectFlag.">" . $site->name . "</option>\n";
        }
        return $output;
    }
}
