<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerProduct extends Model
{
    use HasFactory;

    protected $connection = 'tandc_live';
    protected $table = 'container_product';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'container_id',
        'product_id',
        'cost',
        'rrp',
    ];
    protected $casts = [
		'container_id' => 'int',
		'product_id' => 'int',
        'cost' => 'double',
        'rrp' => 'double',
    ];
    public function container():BelongsTo
    {
        return $this->belongsTo(InboundContainer::class, 'container_id');
    }
    private $_container;
    public function getContainer():InboundContainer|null
    {
        return $this->_container ??= InboundContainer::find($this->container_id);
    }
    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    private $_product;
    public function getProduct():Product|null
    {
        return $this->_product ??= $this->product()->first();
    }
}
