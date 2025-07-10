<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplementalDetails extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'supplemental_details';
}
