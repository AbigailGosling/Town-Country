<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocType extends Model
{
    use HasFactory;

    protected $connection = 'tandc_live';
    protected $table = 'doc_type';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'abbreviation',
    ];
    public static function generateHTMLList($selected = null):string{
		$output = "";
		foreach(self::all() as $docType){
			if ($docType->name == "")continue;
			$selectFlag = ($selected != null && $selected == $docType->id) ? " selected" : "";
			$output = $output . "<option value='".$docType->id."'".$selectFlag.">" . $docType->name . "</option>\n";
		}
		return $output;
	}
}
