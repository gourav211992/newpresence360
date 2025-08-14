<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingListDetail extends Model
{
    use HasFactory;
    protected $table = 'erp_packing_list_details';

    protected $fillable = [
        'plist_id',
        'sale_order_id',
        'packing_number',
        'remarks'
    ];

    public function items()
    {
        return $this -> hasMany(PackingListItem::class, 'plist_detail_id');
    }

    public function sale_order()
    {
        return $this -> belongsTo(ErpSaleOrder::class, 'sale_order_id');
    }
    public function header()
    {
        return $this -> belongsTo(PackingList::class, 'plist_id');
    }

}
