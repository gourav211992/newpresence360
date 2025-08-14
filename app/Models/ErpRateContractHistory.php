<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\FileUploadTrait;
use App\Traits\UserStampTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpRateContractHistory extends Model
{
    use HasFactory,SoftDeletes, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, UserStampTrait;
    protected $table = 'erp_rate_contract_history';

    protected $fillable = [
        'source_id',
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'document_number',
        'document_type',
        'doc_number_type',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'start_date',
        'end_date',
        'document_date',
        'revision_number',
        'document_status',
        'approval_level',
        'remarks',
        'vendor_id',
        'vendor_code',
        'applicable_organizations',
        'payment_term_id',
    ];

    public function source()
    {
        return $this->belongsTo(ErpRateContract::class, 'source_id');
    }

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
    public function vendor()
    {
        return $this -> hasOne(Vendor::class, 'id', 'vendor_id');
    }
    public function media()
    {
        return $this->morphMany(ErpRcMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpRcMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }
    
    public function items()
    {
        return $this -> hasMany(ErpRateContractItem::class, 'rate_contract_id');
    }
    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }   
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class,'created_by','id');
    }
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function isActive($vendorId)
    {
        return $this->where('document_status', 'approved')->orWhere('document_status', 'approval_not_required')->where('end_date', '>=', now())->orWhere('end_date', null)->where('start_date', '<=', now())->where('vendor_id', $vendorId)->exists();
    }


}
