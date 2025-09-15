<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InboundContainerApproval extends Model
{
    use HasFactory;

    protected $table = 'inbound_container_approval';

    protected $connection = 'tandc_live';

    protected $fillable = [
        'user_id',
        'file_id',
        'approved',
    ];

    protected $casts = [
        'user_id'  => 'integer',
        'approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function file():HasOne
    {
        return $this->HasOne(File::class,"id","file_id");
    }
    public function hasFile():bool
    {
        return !$this->file_id == null;
    }
    private $_file;
    public function getFile():File
    {
        return $this->_file ??= File::find($this->file_id);
    }
}
