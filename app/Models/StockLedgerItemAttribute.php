<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLedgerItemAttribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_ledger_item_attributes';

    // Define relationships
    public function stockLedger()
    {
        return $this->belongsTo(StockLedger::class, 'stock_ledger_id');
    }

    public function attributeName()
    {
        return $this->belongsTo(ErpAttributeGroup::class, 'attribute_name');
    }

    public function attributeValue()
    {
        return $this->belongsTo(ErpAttribute::class, 'attribute_value');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function item_attribute()
    {
        return $this->belongsTo(ItemAttribute::class, 'item_attribute_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
