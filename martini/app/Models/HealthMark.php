<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class HealthMark
 *
 * @property int $id
 * @property string $name
 * @property bool $disabled
 *
 * @package App\Models
 */
class HealthMark extends Model
{
    protected $connection = 'tandc_live';
	protected $table = 'health_mark';
	public $timestamps = false;

	protected $casts = [
		'disabled' => 'bool'
	];

	protected $fillable = [
		'name',
		'disabled'
	];
}
