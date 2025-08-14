<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\DynamicFieldsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpTransporterRequest extends Model
{

    use HasFactory,DefaultGroupCompanyOrg, DynamicFieldsTrait,DateFormatTrait;

    protected $fillable =[
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
        'document_no',
        'document_date',
        "doc_no",
        'loading_date_time',
        'vehicle_type',
        'total_weight',
        'uom_id',
        'uom_code',
        'bid_start',
        'bid_end',
        'transporter_ids',
        'selected_bid_ids',
        'reference_number',
        'revision_number',
        'document_status',
        'approval_level',
        'remarks',
    ];
    protected $casts = [
        'transporter_ids' => 'array',
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

    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function pickup()
    {
        return $this->hasMany(ErpTransporterRequestLocation::class, 'transporter_request_id')->where('location_type', 'pick_up');
    }

    public function dropoff()
    {
        return $this->hasMany(ErpTransporterRequestLocation::class, 'transporter_request_id')->where('location_type', 'drop_off');
    }
    public function vehicle()
    {
        return $this->belongsTo(ErpVehicleType::class, 'vehicle_type','id');
    }

    public function createdBy()
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }

    public function bid()
    {
        return $this->belongsTo(ErpTransporterRequestBid::class , "selected_bid_id");     
    }
    public function bids()
    {
        return $this->hasMany(ErpTransporterRequestBid::class, "transporter_request_id")->orderBy('bid_price');
 
    }

    public function location_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', '');
    }
    public function dynamic_fields()
    {
        return $this -> hasMany(ErpTrDynamicField::class, 'header_id');
    }
}
