<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
class PaymentTerm extends Model
{
    use SoftDeletes,Deletable,DefaultGroupCompanyOrg;

    protected $table = 'erp_payment_terms';

    protected $fillable = [
        'name',
        'alias',
        'status',
        'group_id',      
        'company_id',         
        'organization_id' 
    ];

    public function details()
    {
        return $this->hasMany(PaymentTermDetail::class);
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
