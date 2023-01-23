<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PagePermission
 * This class represents outdated permission definitions, DO NOT USE FOR OPERATION
 * @property int $id
 * @property string|null $name
 * @property string|null $file
 * @property int|null $column
 *
 * @package App\Models
 */
class PagePermission extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'page_permissions';
	public $timestamps = false;

	protected $casts = [
		'column' => 'int'
	];

	protected $fillable = [
		'name',
		'file',
		'column'
	];
}
