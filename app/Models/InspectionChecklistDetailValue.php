<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;
class InspectionChecklistDetailValue extends Model
{
    use HasFactory, SoftDeletes,Deletable;

    protected $table = 'erp_inspection_checklist_detail_values';

    protected $fillable = [
        'inspection_checklist_detail_id',
        'value',
    ];

    public function detail()
    {
        return $this->belongsTo(InspectionChecklistDetail::class, 'inspection_checklist_detail_id');
    }
}
