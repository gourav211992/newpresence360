<?php

namespace App\Models;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpProductionWorkOrderHistory extends Model
{
    use HasFactory,DateFormatTrait,DefaultGroupCompanyOrg,FileUploadTrait;
    
    protected $table = "erp_production_work_orders_history";

    protected $fillable = [
        'source_id',
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
        'book' => 'book_id',
        'location' => 'location_id',
        'source' => 'source_id'
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
    public function source()
    {
        return $this->belongsTo(ErpProductionWorkOrder::class, 'source_id');
    }
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    public function pwo_items()
    {
        return $this->hasMany(ErpPwoItemHistory::class, 'pwo_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(Employee::class,'created_by','id');
    }
    public function locations()
    {
        return $this->belongsTo(ErpStore::class,'location_id');
    }

    public function media_files()
    {
        return $this->morphMany(ErpSoMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    public function location_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', '');
    }
}
