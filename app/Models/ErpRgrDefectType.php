<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpRgrDefectType extends Model
{
    use HasFactory;

    protected $table = 'erp_rgr_defect_types';

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'category_id',
        'defect_severity',
    ];

    /**
     * Relationship to defect type details
     */
    public function details()
    {
        return $this->hasMany(ErpRgrDefectTypeDetail::class, 'header_id');
    }
}
