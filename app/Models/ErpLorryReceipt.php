<?php

namespace App\Models;

use App\Models\User;
use App\Models\Customer;
use App\Models\ErpDriver;
use App\Models\ErpVehicleType;
use App\Traits\DateFormatTrait;
use App\Models\ErpRouteMaster;
use App\Traits\FileUploadTrait;
use App\Models\ErpLogisticsLrLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\DefaultGroupCompanyOrg;

class ErpLorryReceipt extends Model
{
    use HasFactory, DefaultGroupCompanyOrg,FileUploadTrait, DateFormatTrait;

    protected $table = 'erp_logistics_lorry_receipt';

    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'book_code',
        'document_type',
        'document_number',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_date',
        'origin_id',
        'destination_id',
        'consignor_id',
        'consignee_id',
        'vehicle_id',
        'vehicle_type_id',
        'distance',
        'freight_charges',
        'driver_id',
        'driver_cash',
        'fuel_price',
        'invoice_no',
        'invoice_value',
        'no_of_bundles',
        'weight',
        'ewaybill_no',
        'gst_paid_by',
        'lr_type',
        'billing_type',
        'load_type',
        'lr_charges',
        'sub_total',
        'total_charges',
        'remarks',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
    'document_date' => 'date',
];

    // Relationships

    public function locations()
    {
        return $this->hasMany(ErpLogisticsLrLocation::class, 'lorry_receipt_id', 'id');
    }

     public function book()
    {
        return $this->belongsTo(Book::class,  'book_id');
    }

    public function source()
    {
        return $this->belongsTo(ErpRouteMaster::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(ErpRouteMaster::class, 'destination_id');
    }

    public function driver()
    {
        return $this->belongsTo(ErpDriver::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(ErpVehicleType::class);
    }


   public function vehicle()
    {
        return $this->belongsTo(ErpVehicle::class, 'vehicle_id');
    }

    public function consignor()
    {
        return $this->belongsTo(Customer::class, 'consignor_id');
    }

    public function consignee()
    {
        return $this->belongsTo(Customer::class, 'consignee_id');
    }

      public function auth_user()
    {
        return $this->belongsTo(AuthUser::class, 'created_by', 'id');
    }

   public function mediaAttachments()
    {
        return $this->hasMany(ErpLogisticLRMedia::class, 'model_id', 'id')
                    ->where('model_name', 'ErpLorryReceipt'); 
    }

       public function media()
    {
        return $this->morphMany(ErpLogisticLRMedia::class, 'model');
    }


}
