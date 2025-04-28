<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseItemAttribute extends Model
{

    use HasFactory;

    protected $table = "erp_expense_item_attributes";
    protected $fillable = [
        'expense_header_id', 
        'expense_detail_id', 
        'item_id',
        'item_code',
        'item_attribute_id', 
        'attr_name', 
        'attr_value'
    ];

    protected $appends = [
    ];

    protected $hidden = ['deleted_at'];

    public $referencingRelationships = [
        'itemAttribute' => 'item_attribute_id',
        'headerAttribute' => 'attr_name',
        'headerAttributeValue' => 'attr_value'
    ];

    public function expenseHeader()
    {
        return $this->belongsTo(ExpenseHeader::class);
    }

    public function expenseDetail()
    {
        return $this->belongsTo(ExpenseDetail::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class);
    }

}