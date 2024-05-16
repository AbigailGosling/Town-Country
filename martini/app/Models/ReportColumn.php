<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class ReportColumn
 * 
 * @property int $id
 * @property array $label
 * @property string $fetch_type
 * @property string $data_type
 * @property string $processing_type
 * @property string $header
 * @property string $cell
 * @property array $pointers
 * @property array $metadata
 *
 * @package App\Models
 */
class ReportColumn extends Model
{
    use HasFactory;
    protected $connection = 'tandc_live';
	protected $table = 'report_columns';
    public $timestamps = false;
    protected $fillable = [
		'label',
        'fetch_type',
        'data_type',
        'processing_type',
        'header',
        'cell',
        'footer',
        'pointers',
        'metadata',
	];
    protected $casts = [
        'label' => 'array',
        'pointers' => 'array',
        'metadata' => 'array',
    ];
    protected ?array $parsedLabel = null;
    public function getLabel(string $mode):string
    {
        if ($this->parsedLabel === null) 
        {
            if (is_string($this->label)) {
                $this->parsedLabel = json_decode($this->label);
                if ($this->parsedLabel === null) 
                {
                    $this->parsedLabel = array("debits"=> $this->label);
                }
            }
            else if (array_is_list($this->label)) {
                $this->parsedLabel = array("debits"=> $this->label[0]);
            }
            else {
                $this->parsedLabel = $this->label;
            }

            if (!array_key_exists("debits",$this->parsedLabel)) {
                foreach ($this->parsedLabel as $str){
                    $this->parsedLabel["debits"] = $str;
                    break;
                }
            }
        }
        if (!array_key_exists($mode,$this->parsedLabel)) $this->parsedLabel[$mode] = $this->parsedLabel["debits"];
        return $this->parsedLabel[$mode];
    }
}
