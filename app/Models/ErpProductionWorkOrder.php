<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ErpProductionWorkOrder extends Model
{
    use HasFactory,DateFormatTrait,DynamicFieldsTrait,DefaultGroupCompanyOrg,FileUploadTrait;
    
    protected $fillable = [
        'organization_id', 
        'group_id', 
        'company_id',
        'location_id',
        'book_id', 
        'book_code', 
        'document_number',
        'document_date',
        'revision_number',
        'revision_date',
        'reference_number',
        'document_status',
        'approval_level',
        'remarks',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'station_wise_consumption',
        'so_tracking_required'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->created_by = $user->auth_user_id;
            }
        });

        static::updating(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->updated_by = $user->auth_user_id;
            }
        });

        static::deleting(function ($model) {
            $user = Helper::getAuthenticatedUser();
            if ($user) {
                $model->deleted_by = $user->auth_user_id;
            }
        });
    }

    public $referencingRelationships = [
        'book' => 'book_id'
    ];

    public function media()
    {
        return $this->morphMany(ProductionWorkOrderMedia::class, 'model');
    }

    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
    
    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }
    
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    public function items()
    {
        return $this->hasMany(ErpPwoItem::class, 'pwo_id');
    }
    public function location()
    {
        return $this->belongsTo(ErpStore::class,'location_id');
    }
    public function store_location()
    {
        return $this->belongsTo(ErpStore::class, 'location_id', 'id');
    }

    public function media_files()
    {
        return $this->morphMany(ProductionWorkOrderMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    public function location_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', '');
    }
    public function mapping()
    {
        return $this->hasMany(PwoSoMapping::class, 'pwo_id');
    } 

    public function last_so()
    {
        $pwoSoMapping = $this->mapping?->count() ? $this->mapping()->whereNotNull('so_id')->first() : null;
        if($pwoSoMapping) {
            return $pwoSoMapping?->so;
        }
        return null;
    }
       public function dynamic_fields()
    {
        return $this -> hasMany(ErpPwoDynamicField::class, 'header_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class,'created_by','id');
    }
}
