<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * Class ReportTable
 *
 * @property int $id
 * @property string $name
 * @property string $mode
 * @property boolean $isSup
 * @property int $version
 *
 * @package App\Models
 */
class ReportTable extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'report_tables';
    public $timestamps = true;
    protected $fillable = [
		'name',
        'mode',
        'version',
        'isSup',
	];
    private Collection $columns;
    public function getColumns():Collection
    {
        if (!isset($this->columns)){
            $reportColIDs = ReportTableColumn::where(["table_id"=>$this->id])->orderBy("order")->get()->pluck("column_id")->toArray();
            $this->columns = new Collection();
            foreach($reportColIDs as $cid)
            {
                $this->columns->add(ReportColumn::findOrFail($cid));
            }
        }
        return $this->columns;
    }
}
