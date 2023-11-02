<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use stdClass;

/**
 * Class User
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
        'sale_rules' => 'array'
    ];
    public function site():BelongsTo
    {
        return $this->belongsTo(Site::class,"site_id","id");
    }
    public function bulkUpdateSaleRule(array $newRules)
    {
        foreach(self::all() as $otherLocation){
            if ($otherLocation->id == $this->id) continue;

            $otherRules = $otherLocation->sale_rules;
            if ($otherLocation->site_id != $this->site_id){
                if (array_key_exists($this->id,$otherRules)){
                    unset($otherRules[$this->id]);
                }
            }
            else {
                if (array_key_exists($otherLocation->id,$newRules)) {
                    $otherRules[$this->id] = true;
                }
                else if (array_key_exists($this->id,$otherRules)){
                    unset($otherRules[$this->id]);
                }
            }
            $otherLocation->sale_rules = $otherRules;
            $otherLocation->save();
        }
        $myRules = [];
        foreach($newRules as $property => $value){
            $myRules[$property] = true;
        }
        $this->sale_rules = $myRules;
        $this->save();
    }
}
