<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class HealthMark
 *
 * @property int $id
 * @property string name
 * @property boolean disabled
 * @package App\Models
 */
class HealthMark extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'health_mark';
	public $timestamps = false;

	protected $casts = [
	];

	protected $dates = [
	];

	protected $fillable = [
		'name',
		'disabled',
	];
}
