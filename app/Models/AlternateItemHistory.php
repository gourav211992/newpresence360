<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class AlternateItemHistory extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'erp_alternate_items_history';

    protected $fillable = [
        'source_id', 
        'alt_item_id',
        'item_id',
        'item_code', 
        'item_name'
    ];


    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
