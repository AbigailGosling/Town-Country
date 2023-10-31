<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property int $site_id
 * @property bool $disabled
 * @property array $rules
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
        'rules' => 'array'
    ];
    public function site():BelongsTo
    {
        return $this->belongsTo(Site::class,"site_id","id");
    }
}
