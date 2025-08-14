<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingListItem extends Model
{
    use HasFactory;
    protected $table = 'erp_packing_list_items';

    protected $fillable = [
        'plist_id',
        'plist_detail_id',
        'sale_order_id',
        'so_item_id',
        'item_id',
        'item_code',
        'item_name',
        'qty'
    ];

    public function detail()
    {
        return $this -> belongsTo(PackingListDetail::class, 'plist_detail_id');
    }

    public function attributes()
    {
        return $this -> hasMany(PackingListItemAttribute::class, 'plist_item_id');
    }
}
