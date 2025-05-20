<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\FileUploadTrait;

class MfgOrderHistory extends Model
{
    use HasFactory,DateFormatTrait,FileUploadTrait,DefaultGroupCompanyOrg;

    protected $table = 'erp_mfg_orders_history';

    protected $fillable = [
        'source_id',
        'production_bom_id',
        // 'production_route_id',
        'store_id',
        'station_id',
        'item_id',
        'customer_id', 
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
        'is_last_station'
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

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'production_bom_id');
    }

    public function productionRoute()
    {
        return $this->belongsTo(ProductionRoute::class, 'production_route_id');
    }

    public function store_location()
    {
        return $this->belongsTo(ErpStore::class, 'store_id', 'id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
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
    
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
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
        return $this->hasMany(MoProductHistory::class, 'mo_product_id');
    }
    
    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}