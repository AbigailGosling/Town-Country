<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IntakeDoc
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $dfile
 * @property string|null $intakeid
 *
 * @package App\Models
 */
class IntakeDoc extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'intakeDocs';
	public $timestamps = false;

	protected $fillable = [
		'name',
		'dfile',
		'intakeid'
	];
}
