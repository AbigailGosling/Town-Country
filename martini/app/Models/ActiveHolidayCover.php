<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ActiveHolidayCover
 * 
 * @property int $id
 * @property int $absent_id
 * @property int $cover_id
 *
 * @package App\Models
 */

class ActiveHolidayCover extends Model
{
    use HasFactory;
	protected $connection = 'tandc_live';
	protected $table = 'active_holiday_cover';
	protected $fillable = [
		'absent_id',
        'cover_id',
	];
    public $timestamps = false;
    public function absentUser():User{
        return User::find($this->absent_id);
    }
    public function coverUser():User{
        return User::find($this->cover_id);
    }
}
