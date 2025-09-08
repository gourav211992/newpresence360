<?php
namespace App\Models\ERP;

use App\Models\ErpAddress;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\OrganizationGroup;
// use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ErpConsignee extends Model
{
    use SoftDeletes;

    protected $table = 'erp_consignees';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'consignee_name',
        'consignee_code',
        'is_customer',
        'is_vendor',
        'email',
        'phone',
        'mobile',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    protected $hidden = ['deleted_at', 'deleted_by'];



    /**
     * A consignee can have multiple addresses (polymorphic).
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(ErpAddress::class, 'addressable');
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

?>
