<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Helpers\ServiceParametersHelper;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'erp_books';

    use HasFactory, DefaultGroupCompanyOrg;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'org_service_id',
        'manual_entry',
        'service_id',
        'book_code',
        'book_name',
        'status',
        'group_id',
        'company_id',
        'organization_id'
    ];

    public function bookType()
    {
        return $this->belongsTo(BookType::class, 'booktype_id');
    }

    public function patterns()
    {
        return $this->hasMany(NumberPattern::class);
    }
    public function levels()
    {
        return $this->hasMany(BookLevel::class, 'book_id');
    }

    public function amendment()
    {
        return $this->hasOne(AmendmentWorkflow::class, 'book_id');
    }

    public function amendments()
    {
        return $this->hasMany(AmendmentWorkflow::class, 'book_id');
    }

    public function org_service()
    {
        return $this -> belongsTo(OrganizationService::class, 'org_service_id');
    }
    public function service()
    {
        return $this -> belongsTo(OrganizationService::class, 'org_service_id');
    }
    public function master_service()
    {
        return $this -> belongsTo(Service::class, 'service_id');
    }

    public function parameters()
    {
        return $this -> hasMany(OrganizationBookParameter::class, 'book_id');
    }
    public function common_parameters()
    {
        return $this -> hasMany(OrganizationBookParameter::class, 'book_id') -> where('type', ServiceParametersHelper::COMMON_PARAMETERS);
    }
    public function gl_parameters()
    {
        return $this -> hasMany(OrganizationBookParameter::class, 'book_id') -> where('type', ServiceParametersHelper::GL_PARAMETERS);
    }

    public function dynamic_fields()
    {
        return $this -> hasMany(BookDynamicField::class, 'book_id');
    }
    
}
