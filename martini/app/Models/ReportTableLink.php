<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class ReportTableLink
 * 
 * @property int $id
 * @property int $report_id
 * @property int $table_id
 *
 * @package App\Models
 */
class ReportTableLink extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'report_table_links';
    public $timestamps = false;
    protected $fillable = [
		'report_id',
        'table_id',
	];
}
