<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;

class HsnTaxPattern extends Model
{
    use HasFactory,Deletable,SoftDeletes;

    protected $table = 'erp_hsn_tax_patterns';

    protected $fillable = [
        'hsn_id',
        'from_price',
        'upto_price',
        'from_date',
        'tax_group_id',
    ];

    protected $dates = ['from_date'];


    public function hsn()
    {
        return $this->belongsTo(Hsn::class);
    }

    public function taxGroup()
    {
        return $this->belongsTo(Tax::class);
    }
}
