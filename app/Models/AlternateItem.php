<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;


class AlternateItem extends Model
{
    use HasFactory,SoftDeletes,Deletable;
    protected $table = 'erp_alternate_items';
    protected $fillable = [
        'alt_item_id',
        'item_id',
        'item_code',
        'item_name',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
