<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DebugLogging
 * 
 * @property int $id
 * @property string $page
 * @property string $request
 * @property int $user_id
 * @property string $session_id
 * @property string $body
 * @property Carbon $timestamp
 *
 * @package App\Models
 */
class DebugLogging extends Model
{
	protected $connection = 'tandc_live';
	protected $table = 'debug_logging';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int'
	];

	protected $dates = [
		'timestamp'
	];

	protected $fillable = [
		'page',
		'request',
		'user_id',
		'session_id',
		'body',
		'timestamp'
	];
}
