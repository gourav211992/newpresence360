<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseItemAttributeHistory extends Model
{

    use HasFactory;

    protected $table = "erp_expense_item_attribute_histories";
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
        return $this->belongsTo(ExpenseHeader::class, 'header_id');
    }

    public function headerHistory()
    {
        return $this->belongsTo(ExpenseHeaderHistory::class, 'header_history_id');
    }

    public function detail()
    {
        return $this->belongsTo(ExpenseDetail::class, 'detail_id');
    }

    public function detailHistory()
    {
        return $this->belongsTo(ExpenseDetailHistory::class, 'detail_history_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function attribute()
    {
        return $this->belongsTo(ExpenseItemAttribute::class, 'attribute_id');
    }

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class, 'item_attribute_id');
    }

}