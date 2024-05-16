<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class ReportTableColumn
 * 
 * @property int $id
 * @property int $report_table_id
 * @property int $report_column_id
 *
 * @package App\Models
 */
class ReportTableColumn extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'report_table_column';
    public $timestamps = false;
    protected $fillable = [
		'table_id',
        'column_id',
        'order',
	];
}
