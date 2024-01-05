<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Cut
 * 
 * @property int $id
 * @property string|null $species_id
 * @property string|null $name
 * @property string|null $cutgroup_id
 * @property int|null $warning
 * @property int|null $danger
 * @property bool $disabled
 *
 * @package App\Models
 */
class Cut extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'cuts';
	public $timestamps = false;

	protected $casts = [
		'warning' => 'int',
		'danger' => 'int',
		'disabled' => 'bool'
	];

	protected $fillable = [
		'species_id',
		'name',
		'cutgroup_id',
		'warning',
		'danger',
		'disabled'
	];
	public function cutgroup(): BelongsTo
    {
        return $this->belongsTo(CutGroup::class, 'cutgroup_id', 'id');
    }
}
