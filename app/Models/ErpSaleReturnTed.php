<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSaleReturnTed extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_return_id',
        'sale_return_item_id',
        'ted_type',
        'ted_level',
        'ted_id',
        'ted_group_code',
        'ted_name',
        'assessment_amount',
        'ted_percentage',
        'ted_amount',
        'applicable_type',
    ];

    protected $hidden = ['deleted_at'];

    public function header()
    {
        return $this->belongsTo(ErpSaleReturn::class, 'sale_return_id');
    }
    public function taxDetail()
    {
        return $this->belongsTo(TaxDetail::class, 'ted_id');
    }

}
