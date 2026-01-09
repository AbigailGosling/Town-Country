<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SupplierReturnAttachment
 *
 * @property int $id
 * @property int $user_id
 * @property int $return_id
 * @property int|null $file_id
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SupplierReturnAttachment extends Model
{
    protected $connection = 'tandc_live';
    protected $table = 'supplier_return_attachment';
    public $timestamps = true;
    protected $fillable = [
        'user_id',
        'return_id',
        'file_id',
        'comments',
        'product_collected',
    ];

    protected $casts = [
        'user_id'   => 'integer',
        'return_id' => 'integer',
        'file_id'   => 'integer',
        'comments'  => 'string',
        'product_collected'=>'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplierReturn()
    {
        return $this->belongsTo(SupplierReturn::class, 'return_id');
    }

    public function file()
    {
        return $this->hasOne(File::class, 'id','file_id');
    }
}
