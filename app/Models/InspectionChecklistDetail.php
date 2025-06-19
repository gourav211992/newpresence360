<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;
class InspectionChecklistDetail extends Model
{
    use HasFactory, SoftDeletes,Deletable;

    protected $table = 'erp_inspection_checklist_details';

    protected $fillable = [
        'header_id',
        'name',
        'data_type',
        'description',
        'mandatory'
    ];
    public function checklist()
    {
        return $this->belongsTo(InspectionChecklist::class, 'header_id');
    }

    public function values()
    {
        return $this->hasMany(InspectionChecklistDetailValue::class, 'inspection_checklist_detail_id');
    }
}
