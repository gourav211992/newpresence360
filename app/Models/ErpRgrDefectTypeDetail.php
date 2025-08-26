<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRgrDefectTypeDetail extends Model
{
    use HasFactory;

    protected $table = 'erp_rgr_defect_type_details';

    protected $fillable = [
        'header_id',
        'type',
    ];

  
    public function header()
    {
        return $this->belongsTo(ErpRgrDefectType::class, 'header_id');
    }
}