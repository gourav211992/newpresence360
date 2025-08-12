<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MrnAssetDetail extends Model
{

    use HasFactory;

    protected $table = "erp_mrn_asset_details";
    protected $fillable = [
        'header_id', 
        'detail_id', 
        'asset_category_id', 
        'item_id', 
        'asset_code', 
        'asset_name', 
        'capitalization_date', 
        'brand_name', 
        'model_no', 
        'estimated_life', 
        'salvage_value'
    ];

    protected $hidden = ['deleted_at'];

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class, 'header_id');
    }

    public function source()
    {
        return $this->hasOne(MrnAssetDetailHistory::class, 'source_id');
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class, 'detail_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

}