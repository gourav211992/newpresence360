<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrnAttribute extends Model
{

    use HasFactory;

    protected $table = "erp_mrn_attributes";
    protected $fillable = [
        'mrn_header_id', 
        'mrn_detail_id', 
        'item_id',
        'item_attribute_id', 
        'attr_name', 
        'attr_value'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'itemAttribute' => 'item_attribute_id',
        'headerAttribute' => 'attribute_name',
        'headerAttributeValue' => 'attribute_value'
    ];

    protected $hidden = ['deleted_at'];

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class);
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function attributeName()
    {
        return $this->belongsTo(ErpAttributeGroup::class, 'attr_name');
    }

    public function attributeValue()
    {
        return $this->belongsTo(ErpAttribute::class, 'attr_value');
    }

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class, 'item_attribute_id');
    }

    public function headerAttribute()
    {
        return $this->hasOne(AttributeGroup::class,'id' ,'attr_name');
    }

    public function headerAttributeValue()
    {
        return $this->hasOne(Attribute::class,'id','attr_value');
    }

}