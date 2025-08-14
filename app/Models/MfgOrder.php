<?php

namespace App\Models;

use App\Traits\DynamicFieldsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\FileUploadTrait;

class MfgOrder extends Model
{
    use HasFactory,DateFormatTrait,DynamicFieldsTrait,FileUploadTrait,DefaultGroupCompanyOrg;

    protected $table = 'erp_mfg_orders';

    protected $fillable = [
        'mo_id',
        'production_bom_id',
        'production_route_id',
        'store_id',
        'sub_store_id',
        'station_id',
        'item_id',
        'organization_id', 
        'group_id', 
        'company_id', 
        'book_id', 
        'book_code', 
        'document_number', 
        'document_date', 
        'doc_number_type', 
        'doc_reset_pattern', 
        'doc_prefix', 
        'doc_suffix', 
        'doc_no', 
        'document_status', 
        'revision_number', 
        'revision_date', 
        'remarks', 
        'approval_level',
        'is_last_station',
        'machine_id'
    ];

    protected $appends = [
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

    
    // public $referencingRelationships = [
    //     'vendor' => 'vendor_id',
    //     'bill_address' => 'billing_address',
    //     'ship_address' => 'shipping_address',
    //     'currency' => 'currency_id',
    //     'paymentTerm' => 'payment_term_id',
    //     'org_currency' => 'org_currency_id',
    //     'comp_currency' => 'comp_currency_id',
    // ];

    public function getSoTrackingRequiredAttribute()
    {
        $soTrackingRequired = 'no';
        $firstMoProd = $this->moProducts->first();
        if(isset($firstMoProd) && $firstMoProd?->pwoMapping?->pwo?->so_tracking_required) {
            $soTrackingRequired = $firstMoProd?->pwoMapping?->pwo?->so_tracking_required ?? 'no';
        }
        return $soTrackingRequired;
    }

    public function store_location()
    {
        return $this->belongsTo(ErpStore::class, 'store_id', 'id');
    }
    
    public function sub_store()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }

    public function machine()
    {
        return $this->belongsTo(ErpMachine::class, 'machine_id');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'production_bom_id');
    }

    public function productionRoute()
    {
        return $this->belongsTo(ProductionRoute::class, 'production_route_id');
    }
    
    public function media()
    {
        return $this->morphMany(MoMedia::class, 'model');
    }

    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
    
    public function source()
    {
        return $this->hasOne(MfgOrderHistory::class, 'source_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function getDisplayProductionTypeAttribute()
    {
        $t = str_replace('-', ' ', $this->production_type);
        return ucwords($t);
    }

    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }

    public function moProducts()
    {
        return $this->hasMany(MoProduct::class, 'mo_id');
    }
    
    public function moItems()
    {
        return $this->hasMany(MoItem::class, 'mo_id');
    } 

    public function last_so()
    {
        $moProduct = $this->moProducts?->count() ? $this->moProducts()->whereNotNull('so_id')->first() : null;
        if($moProduct) {
            return $moProduct?->so;
        }
        return null;
    }
    
    public function items()
    {
        return $this->hasMany(MoItem::class, 'mo_id');
    } 

    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
    
    public function dynamic_fields()
    {
        return $this -> hasMany(ErpMoDynamicField::class, 'header_id');
    }
}
