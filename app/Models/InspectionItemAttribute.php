<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InspectionItemAttribute extends Model
{

    use HasFactory, SoftDeletes;

    protected $table = "erp_insp_attributes";
    protected $fillable = [
        'header_id', 
        'detail_id', 
        'item_id', 
        'item_code', 
        'item_attribute_id', 
        'attr_name', 
        'attr_value'
    ];

    protected $appends = [
    ];

    protected $hidden = ['deleted_at'];

    public function header()
    {
        return $this->belongsTo(InspectionHeader::class, 'header_id');
    }

    public function detail()
    {
        return $this->belongsTo(InspectionDetail::class, 'detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemAttribute()
    {
        return $this->belongsTo(ItemAttribute::class);
    }

    public function attributeName()
    {
        return $this->belongsTo(ErpAttributeGroup::class, 'attr_name');
    }

    public function attributeValue()
    {
        return $this->belongsTo(ErpAttribute::class, 'attr_value');
    }

}