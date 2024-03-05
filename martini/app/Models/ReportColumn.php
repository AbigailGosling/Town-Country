<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class ReportColumn
 * 
 * @property int $id
 * @property string $label
 * @property string $fetch_type
 * @property string $data_type
 * @property string $processing_type
 * @property string $html_header
 * @property string $html_cell
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
        'html_header',
        'html_cell',
        'html_footer',
        'pointers',
        'metadata',
	];
    protected $casts = [
        'pointers' => 'array',
        'metadata' => 'array',
    ];
}
