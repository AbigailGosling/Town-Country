<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldUser extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'users';
	public $timestamps = false;
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
    ];
}
