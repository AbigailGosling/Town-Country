<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Species
 * 
 * @property int $id
 * @property string|null $name
 *
 * @package App\Models
 */
class Species extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'species';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];
	public static function generateHTMLList(int $selected = null):string{
		$output = "";
		foreach(self::all() as $site){
			if ($site->name == "")continue;
			$selectFlag = ($selected != null && $selected == $site->id) ? " selected" : "";
			$output = $output . "<option value='".$site->id."'".$selectFlag.">" . $site->name . "</option>\n";
		}
		return $output;
	}
}
