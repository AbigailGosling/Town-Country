<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Location
 *
 * @property int $id
 * @property string $name
 * @property int $site_id
 * @property bool $disabled
 * @property array $sale_rules
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Location extends Model
{
    protected $connection = 'tandc_live';
	protected $table = 'location';

    protected $casts = [
		'site_id' => 'int',
        'disabled' => 'bool',
        'sale_rules' => 'array'
    ];

	protected $fillable = [
		'site_id',
		'name',
		'sale_rules',
		'disabled'
	];
    public function site():BelongsTo
    {
        return $this->belongsTo(Site::class,"site_id","id");
    }
    public function bulkUpdateSaleRule(array $newRules)
    {
        $myRules = [];
        foreach($newRules as $property => $value)
        {
            $myRules[$property] = true;
        }
        $myRules[$this->id] = true;
        $this->sale_rules = $myRules;
        $this->save();
        foreach(self::all() as $otherLocation){
            if ($otherLocation->id == $this->id) continue;

            $otherRules = $otherLocation->sale_rules;
            if ($otherLocation->site_id != $this->site_id){
                if (array_key_exists($this->id,$otherRules)){
                    unset($otherRules[$this->id]);
                }
            }
            else {
                if (array_key_exists($otherLocation->id,$myRules))
                {
                    $otherRules = $myRules;
                }
                else
                {
                    foreach($myRules as $property => $value)
                    {
                        unset($otherRules[$property]);
                    }
                }

            }
            $otherLocation->sale_rules = $otherRules;
            $otherLocation->save();
        }

    }
}
