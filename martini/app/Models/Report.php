<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Report
 * 
 * @property int $id
 * @property int $author_id
 * @property User $author
 * @property string $name
 *
 * @package App\Models
 */
class Report extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'reports';
    public $timestamps = true;
    protected $fillable = [
		'author_id',
        'name',
	];
    public function getReportVersions():Collection 
    {
        return $this->report_versions()->get();
    }
    public function report_versions():HasMany 
    {
        return self::hasMany(ReportVersion::class,"report_id","id");
    }
}
