<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Class InboundContainer
 *
 * @property int $id
 * @property string $internal_number
 * @property string $origin_port
 * @property \Illuminate\Support\Carbon $eta
 * @property bool $admin_approved
 * @property bool $arrived
 * @property string|null $vessel
 * @property int $temperature_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InboundContainer extends Model
{
    use HasFactory;
    protected $table = 'inbound_container';
	protected $connection = 'tandc_live';
    public $timestamps = true;
    protected $fillable = [
        'internal_number',
        'origin_port',
        'eta',
        'admin_approved',
        'arrived',
        'vessel',
        'temperature_id',
        'site_id',
    ];
    protected $casts = [
        'eta' => 'date',
        'admin_approved' => 'boolean',
        'arrived' => 'boolean',
        'temperature_id' => 'integer',
        'site_id' => 'integer',
    ];
    public function containerProducts()
    {
        return $this->hasMany(ContainerProduct::class, 'container_id','id');
    }
    public function getProducts():Collection
    {
        return ContainerProduct::where("container_id",$this->id)->get();
    }
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id','id');
    }
}
