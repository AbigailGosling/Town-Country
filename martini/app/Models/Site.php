<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property bool $disabled
 * @property string $abbreviation
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Site extends Model
{
    protected $connection = 'tandc_live';
	protected $table = 'site';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
    ];
	protected $dates = [
	];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'disabled' => 'bool',
    ];
    public function locations():HasMany
    {
        return $this->hasMany(Location::class,"site_id","id");
    }
    public static function generateOldHTMLList($selected = "") :string {
        $output = "";
        foreach (self::where("disabled",false)->get() as $site) {
            $onlyOne = true;
			if ($site->locations()->where("disabled",false)->count() == 0) continue;
            else if ($site->locations()->where("disabled",false)->count() > 1) {
                $onlyOne = false;
            }
            foreach ($site->locations()->where("disabled",false)->orderBy("name")->get() as $location) {
                //$abbr = ($onlyOne && strlen($site->abbreviation)>0) ? "" : $site->abbreviation . " ";
                $abbr= "";
                $selectFlag = ($selected != "" && $selected == $location->name) ? " selected" : "";
                $output = $output . "<option value='".$location->id."'".$selectFlag.">" . $abbr . $location->name . "</option>\n";
            }
        }
        return $output;
    }
    public static function generateHTMLList($selected = "") :string {
        $output = "";
        foreach (self::where("disabled",false)->get() as $site) {
            $selectFlag = ($selected != "" && $selected == $site->name) ? " selected" : "";
			$output = $output . "<option value='".$site->id."'".$selectFlag.">" . $site->name . "</option>\n";
        }
        return $output;
    }
}
