<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
class InspectionChecklist extends Model
{
    use HasFactory, SoftDeletes,DefaultGroupCompanyOrg,Deletable;

    protected $table = 'erp_inspection_checklists';

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'name',
        'description',
        'type',
        'status',
    ];
    public function details()
    {
        return $this->hasMany(InspectionChecklistDetail::class, 'header_id');
    }
}
