<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Brand
 *
 * @property int $id
 * @property string|null $name
 * @property bool $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @package App\Models
 */
class Brand extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'brands';
	public $timestamps = false;

	protected $fillable = [
		'name',
        'deleted'
	];
}
