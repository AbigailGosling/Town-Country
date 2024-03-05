<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class ReportColumn
 * 
 * @property int $id
 * @property int $report_version_id
 * @property int $report_column_id
 *
 * @package App\Models
 */
class ReportVersionColumn extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'report_version_column';
    public $timestamps = false;
    protected $fillable = [
		'report_version_id',
        'report_column_id',
        'order',
	];
}
