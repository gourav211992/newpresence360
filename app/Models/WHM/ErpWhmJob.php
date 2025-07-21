<?php

namespace App\Models\WHM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpWhmJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'morphable_id',
        'morphable_type',
        'type',
        'status',
        'deviation_qty',
        'deviation_approved_by',
        'deviation_approved_at',
        'job_closed_at',
    ];

    public function morphable()
    {
        return $this->morphTo();
    }

    public function itemUniqueCodes()
    {
        return $this->hasMany(ErpItemUniqueCode::class, 'job_id', 'id');
    }

}