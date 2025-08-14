<?php

namespace App\Models\JobOrder;

use App\Models\DiscountMaster;
use App\Models\ExpenseMaster;
use App\Models\TaxDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrderTed extends Model
{
    use HasFactory;
    protected $table = 'erp_job_order_ted';
    protected $fillable = [
        'jo_id',
        'jo_product_id',
        'ted_type',
        'ted_level',
        'ted_id',
        'ted_name',
        'assessment_amount',
        'ted_perc',
        'ted_amount',
        'applicable_type',
    ];
    protected $appends = [
        'ted_name'
    ];
    public $referencingRelationships = [
        'taxDetail' => 'ted_id'
    ];
    public function getTedNameAttribute()
    {
        $tedName = null;
        $tedId = $this->ted_id ?? null;
        if (!$tedId) {
            return null;
        }
        switch ($this->ted_type ?? null) {
            case 'Tax':
                $tedName = TaxDetail::where('id', $tedId)->value('tax_type');
                break;
    
            case 'Expense':
                $tedName = ExpenseMaster::where('id', $tedId)->value('name');
                break;
    
            case 'Discount':
                $tedName = DiscountMaster::where('id', $tedId)->value('name');
                break;
    
            default:
                $tedName = null;
                break;
        }
        return $tedName;
    }
    public function jo()
    {
        return $this->belongsTo(JobOrder::class,'jo_id');
    }
    public function joProduct()
    {
        return $this->belongsTo(JoProduct::class,'jo_product_id');
    }
    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id', 'id');
    }
}
