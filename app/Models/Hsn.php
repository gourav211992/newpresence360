<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;
use App\Traits\DefaultGroupCompanyOrg;
class Hsn  extends Model
{
    use HasFactory,Deletable,DefaultGroupCompanyOrg;
    protected $table = 'erp_hsns';
    protected $fillable = [
        'type',
        'hsn_master_id',
        'code',
        'description',
        'status',
        'valid_from', 
        'valid_to', 
        'group_id',     
        'company_id',       
        'organization_id',
    ];
    public function tax()
    {
        return $this->belongsTo(Tax::class,'tax_id');
    }
    public function taxPatterns()
    {
        return $this->hasMany(HsnTaxPattern::class);
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
