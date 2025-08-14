<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PRItemAttributeHistory extends Model
{

    use HasFactory;

    protected $table = "erp_purchase_return_item_attributes_history";
    protected $fillable = [
        'header_id', 
        'header_history_id', 
        'detail_id', 
        'detail_history_id', 
        'item_id', 
        'item_code', 
        'attribute_id', 
        'item_attribute_id', 
        'attr_name', 
        'attr_value'
    ];

    protected $appends = [
    ];

    protected $hidden = ['deleted_at'];

    public function header()
    {
        return $this->belongsTo(PRHeader::class, 'header_id');
    }

    public function detail()
    {
        return $this->belongsTo(PRDetail::class, 'detail_id');
    }

    public function headerHistory()
    {
        return $this->belongsTo(PRHeaderHistory::class, 'header_history_id');
    }

    public function detailHistory()
    {
        return $this->belongsTo(PRDetailHistory::class, 'detail_history_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class);
    }

    public function attribute()
    {
        return $this->belongsTo(PRItemAttribute::class, 'attribute_id');
    }

}