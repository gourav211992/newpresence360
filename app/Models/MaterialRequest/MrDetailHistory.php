<?php
namespace App\Models\MaterialRequest;

use App\Models\User;
use App\Helpers\Helper;
use App\Models\Address;
use App\Models\Customer;
use App\Models\InvoiceBook;
use App\Models\Item;
use App\Models\Organization;
use App\Models\Unit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MrDetailHistory extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;
    protected $table = 'erp_mr_detail_histories';

    protected $fillable = [
        'header_id',
        'header_history_id',
        'detail_id',
        'item_id',
        'item_code',
        'item_name',
        'hsn_id',
        'hsn_code',
        'uom_id',
        'uom_code',
        'quantity',
        'inventory_uom',
        'inventory_uom_id',
        'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'basic_value',
        'discount_amount',
        'header_discount_amount',
        'net_value',
        'sgst_percentage',
        'cgst_percentage',
        'igst_percentage',
        'tax_value',
        'item_exp_amount',
        'header_exp_amount',
        'remark',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $reportHeaders = [
        [
            "header" => ["mrn", "Mrn"],
            "components" => [
                "mrn_code" => 'Mrn Code',
                "mrn_type" => 'Mrn Type',
                "mrn_number" => 'Mrn Number',
                "mrn_date" => 'Mrn Date',
                "invoice_number" => 'Invoice Number',
                "invoice_date" => 'Invoice Date',
                "transporter_name" => 'Transporter Name',
                "vehicle_number" => 'Vehicle No.',
            ],
        ],

        [
            "header" => ["item", "Item"],
            "components" => [
                "item_name" => 'Item Name',
                "item_quantity" => 'Item Quantity',
                "item_uom" => 'Item UOM',
            ]
        ]
    ];

    public function getReportHeaders()
    {
        return $this->reportHeaders;
    }

    public function mrnHeader()
    {
        return $this->belongsTo(MrHeader::class);
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrDetail::class);
    }

    public function mrnHeaderHistory()
    {
        return $this->belongsTo(MrHeaderHistory::class);
    }

    public function attributes()
    {
        return $this->hasMany(MrItemAttributeHistory::class, 'mrn_detail_history_id');
    }

    public function extraAmounts()
    {
        return $this->belongsTo(MrTedHistory::class, 'mrn_detail_history_id');
    }

    public function itemDiscount()
    {
        return $this->hasMany(MrTedHistory::class, 'mrn_detail_history_id')->where('ted_level', 'D')->where('ted_type','Discount');
    }

    public function taxes()
    {
        return $this->hasMany(MrTedHistory::class, 'mrn_detail_history_id')->where('ted_type','Tax');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
}


