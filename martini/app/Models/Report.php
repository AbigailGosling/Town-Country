<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Report
 * 
 * @property int $id
 * @property int $author_id
 * @property string $name
 * @property string $mode
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
    public function getAuthor():User
    {
        return $this->author()->get()->first();
    }
    public function author():BelongsTo
    {
        return $this->belongsTo(User::class,"author_id","id");
    }
    private Collection $tables;
    public function getTables():Collection 
    {
        if (!isset($this->tables)){
            $this->tables = new Collection;
            $ids = ReportTableLink::where("report_id",$this->id)->orderBy('order')->get()->pluck("table_id")->toArray();
            foreach($ids as $id) $this->tables[] = ReportTable::find($id);
        }
        return $this->tables;
    }
    public function report_tables():HasMany 
    {
        return self::hasMany(ReportTable::class,"report_id","id");
    }
}
