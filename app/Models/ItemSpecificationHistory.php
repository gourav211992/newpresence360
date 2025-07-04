<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemSpecificationHistory extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'erp_item_specifications_history';

    protected $fillable = [
        'source_id',
        'item_id',
        'group_id',
        'specification_id',
        'specification_name',
        'value'
    ];
    public function item()
    {
        return $this->belongsTo(ItemHistory::class);
    }

    public function group()
    {
        return $this->belongsTo(ProductSpecification::class);
    }

    public function specification()
    {
        return $this->belongsTo(ProductSpecificationDetail::class);
    }
}
