<?php

namespace App\Models\WHM;

use App\Models\Attribute;
use App\Models\Employee;
use App\Models\ErpStore;
use App\Models\ErpSubStore;
use App\Models\ErpVendor;
use App\Models\ErpRepMedia;
use App\Models\Item;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FileUploadTrait;

class ErpItemUniqueCode extends Model
{
    use HasFactory,FileUploadTrait;
    protected $connection = 'mysql';
    protected $table = 'erp_item_unique_codes';
    protected $fillable = [
        'uid',
        'job_id',
        'morphable_id',
        'morphable_type',
        'group_id',
        'company_id',
        'organization_id',
        'store_id',
        'sub_store_id',
        'asset_id',
        'book_id',
        'book_code',
        'doc_type',
        'doc_no',
        'doc_date',
        'item_id',
        'item_attributes',
        'item_name',
        'item_code',
        'item_uid',
        'type',
        'utilized_id',
        'storage_point_id',
        'vendor_id',
        'qty',
        'status',
        'action_by',
        'action_at',
        'job_type',
        'trns_type',
        'batch_id',
        'batch_number',
        'manufacturing_year',
        'expiry_date',
        'serial_no',
        'reference_type',
        'reference_detail_id',
        'reference_no',
        'packet_no',
        'total_packets',
    ];

    // protected  $casts = [
    //     'item_attributes' => 'array'
    // ];

    public function morphable()
    {
        return $this->morphTo();
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function job()
    {
        return $this->belongsTo(ErpWhmJob::class, 'job_id');
    }

    public function actionBy()
    {
        return $this->belongsTo(Employee::class, 'action_by');
    }

    public function vendor()
    {
        return $this->belongsTo(ErpVendor::class, 'vendor_id');
    }

    public function storagePoint()
    {
        return $this->belongsTo(ErpWhDetail::class, 'storage_point_id');
    }

    public function subStore()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function getItemAttributesAttribute($value)
    {
        // First decode stringified JSON to string or array
        $decoded = json_decode($value, true);

        // If still a string, decode again
        if (is_string($decoded)) {
            return json_decode($decoded, true);
        }

        return $decoded;
    }

     public function media()
    {
        return $this->morphMany(ErpRepMedia::class, 'model');
    }

}
