<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;


class Tax  extends Model
{
    use HasFactory,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_taxes';
    
    protected $fillable = [
        'tax_group',
        'description',
        'tax_category',
        'tax_type',
        'status',
        'applicability_type',
        'group_id', 
        'company_id',
        'organization_id', 
    ];
    public function hsn()
    {
        return $this->hasMany(Hsn::class);
    }

    public function taxDetails()
    {
        return $this->hasMany(TaxDetail::class);
    }

    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

}
