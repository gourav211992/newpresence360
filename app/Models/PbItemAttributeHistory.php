<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PbItemAttributeHistory extends Model
{

    use HasFactory;

    protected $table = "erp_pb_item_attribute_histories";
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
        return $this->belongsTo(PbHeader::class, 'header_id');
    }

    public function detail()
    {
        return $this->belongsTo(PbDetail::class, 'detail_id');
    }

    public function headerHistory()
    {
        return $this->belongsTo(PbHeaderHistory::class, 'header_history_id');
    }

    public function detailHistory()
    {
        return $this->belongsTo(PbDetailHistory::class, 'detail_history_id');
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
        return $this->belongsTo(PbItemAttribute::class, 'attribute_id');
    }

}