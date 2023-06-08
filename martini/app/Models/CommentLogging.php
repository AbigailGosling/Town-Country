<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommentLogging
 * 
 * @property int $id
 * @property string $type
 * @property int $user_id
 * @property int $entity_id
 * @property string $body
 * @property Carbon $datetime
 *
 * @package App\Models
 */
class CommentLogging extends Model
{
protected $connection = 'tandc_live';
	protected $table = 'comment_logging';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'entity_id' => 'int'
	];

	protected $dates = [
		'datetime'
	];

	protected $fillable = [
		'type',
		'user_id',
		'entity_id',
		'body',
		'datetime'
	];
}
