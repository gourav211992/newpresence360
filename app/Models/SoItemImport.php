<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoItemImport extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'erp_so_item_imports';
    protected $fillable = [
        'item_id',
        'item_code',
        'uom_id',
        'uom_code',
        'attributes',
        'qty',
        'rate',
        'delivery_date',
        'remarks',
        'is_migrated',
        'reason',
        'created_by'
    ];

    protected $casts = [
        'reason' => 'array',
        'attributes' => 'array'
    ];

    public function item()
    {
        return $this -> belongsTo(Item::class, 'item_id');
    }

}
