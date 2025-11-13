<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

    protected $connection = 'tandc_live';

    protected $fillable = [
        'uuid',
        'original_name',
        'mime_type',
        'size',
    ];

}
