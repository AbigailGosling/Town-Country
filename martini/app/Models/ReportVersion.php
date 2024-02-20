<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ReportVersion
 * 
 * @property int $id
 * @property int $report_id
 * @property int $version
 *
 * @package App\Models
 */
class ReportVersion extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'report_versions';
    public $timestamps = true;
    protected $fillable = [
		'report_id',
        'version',
	];
    public function getReport():Report
    {
        return $this->report()->get()->first();
    }
    public function report():BelongsTo
    {
        return self::belongsTo(Report::class,"report_id","id");
    }
}
