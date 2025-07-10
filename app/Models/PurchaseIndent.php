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

class PurchaseIndent extends Model
{
    use HasFactory,DateFormatTrait,DynamicFieldsTrait,DefaultGroupCompanyOrg,FileUploadTrait;

    protected $table = 'erp_purchase_indents';
    
    protected $fillable = [
        'organization_id', 
        'group_id', 
        'company_id',
        'department_id',
        'store_id',
        'sub_store_id',
        'requester_type',
        'user_id',
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
        'org_currency_id',
        'org_currency_code',
        'org_currency_exg_rate',
        'comp_currency_id',
        'comp_currency_code',
        'comp_currency_exg_rate',
        'group_currency_id',
        'group_currency_code',
        'group_currency_exg_rate',
        'so_tracking_required',
        'procurement_type'
    ];

    protected $appends = [
        'requester_name',
        'department_name'
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

    public function getSoIdAttribute()
    {
        return $this->items
        ->pluck('so_id')
        ->filter()
        ->unique()
        ->values()
        ->toArray();
    }

    public function requester()
    {
        return $this->belongsTo(AuthUser::class, 'user_id', 'id');
    }

    public function media()
    {
        return $this->morphMany(PurchaseIndentMedia::class, 'model');
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
        return $this->hasOne(PurchaseIndentHistory::class, 'source_id');
    }

    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function sub_store()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }
    public function requester_name()
    {
        $modelType = $this->requester_type; // e.g., 'User', 'Department'
        if (!$modelType) {
            return null;
        }

        // Map type to actual model class
        $modelMap = [
            'User' => \App\Models\AuthUser::class,
            'Department' => \App\Models\Department::class,
            // Add other mappings as needed
        ];

        $modelClass = $modelMap[$modelType] ?? "App\\Models\\$modelType";

        if (!class_exists($modelClass)) {
            return null;
        }

        $foreignKey = strtolower($modelType) . '_id';

        if (!isset($this->$foreignKey)) {
            return null;
        }

        return optional($modelClass::find($this->$foreignKey))->name;
    }


    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function pi_items()
    {
        return $this->hasMany(PiItem::class, 'pi_id');
    }

    public function items()
    {
        return $this->hasMany(PiItem::class, 'pi_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function getRequesterNameAttribute()
    {
        $userId = $this -> user_id ?? $this -> created_by;
        $authUser = AuthUser::find($userId);
        return $authUser ?-> name;
    }
    public function getDepartmentNameAttribute()
    {
        return $this -> department ?-> name;
    }
       public function dynamic_fields()
    {
        return $this -> hasMany(ErpPiDynamicField::class, 'header_id');
    }
}
