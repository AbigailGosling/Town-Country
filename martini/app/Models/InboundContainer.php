<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboundContainer extends Model
{
    use HasFactory;

    // Table name (optional if it matches the plural of the model name)
    protected $table = 'inbound_container';
	protected $connection = 'tandc_live';
    // Primary key
    protected $primaryKey = 'id';

    // Auto-incrementing key type
    protected $keyType = 'int';
    public $incrementing = true;

    // Timestamps (created_at & updated_at are present, so this can stay true)
    public $timestamps = true;

    // Mass assignable attributes
    protected $fillable = [
        'internal_number',
        'origin_port',
        'eta',
    ];

    // Date casting
    protected $casts = [
        'eta' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    // Relationships (optional, if you have Container and Product models)
    public function containerProducts()
    {
        return $this->hasMany(ContainerProduct::class, 'container_id','id');
    }
    public function getProducts():Collection
    {
        return ContainerProduct::where("container_id",$this->id)->get();
    }
}
