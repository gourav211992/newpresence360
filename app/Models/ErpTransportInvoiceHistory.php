<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\FileUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\DynamicFieldsTrait;
use App\Traits\UserStampTrait;


class ErpTransportInvoiceHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, FileUploadTrait, DateFormatTrait, UserStampTrait, DynamicFieldsTrait;


    protected $table = 'erp_transport_invoices_history';

    public $referencingRelationships = [
        'customer' => 'customer_id',
        'currency' => 'currency_id',
        'payment_terms' => 'payment_term_id'
    ];

    public function customer()
    {
        return $this -> hasOne(ErpCustomer::class, 'id', 'customer_id');
    }

    public function currency()
    {
        return $this -> hasOne(ErpCurrency::class, 'id', 'currency_id');
    }
    public function payment_terms()
    {
        return $this -> hasOne(ErpPaymentTerm::class, 'id', 'payment_term_id');
    }

    public function items()
    {
        return $this -> hasMany(ErpTIInvoiceItemHistory::class, 'ti_invoice_id');
    }
     public function location_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type',  'location');
    }
public function irnDetail()
    {
        return $this->morphOne(ErpEinvoice::class, 'morphable', 'morphable_type', 'morphable_id');
    }
    public function expense_ted()
    {
        return $this -> hasMany(ErpTransportInvoiceTedHistory::class, 'transport_invoice_id') -> where('ted_level', 'H') -> where('ted_type', 'Expense');
    }
    public function discount_ted()
    {
        return $this -> hasMany(ErpTransportInvoiceTedHistory::class, 'transport_invoice_id') -> where('ted_level', 'H') -> where('ted_type', 'Discount');
    }
    public function billing_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'billing');
    }
    public function shipping_address_details()
    {
        return $this->morphOne(ErpAddress::class, 'addressable', 'addressable_type', 'addressable_id') -> where('type', 'shipping');
    }
    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }
    public function item_locations()
    {
        return $this -> hasMany(ErpInvoiceItemLocationHistory::class, 'transport_invoice_id');
    }

    public function media()
    {
        return $this->morphMany(ErpSiMedia::class, 'model');
    }
    public function media_files()
    {
        return $this->morphMany(ErpSiMedia::class, 'model') -> select('id', 'model_type', 'model_id', 'file_name');
    }

    // public function sale_order_id()
    // {
    //     $item = $this -> items() -> first();
    //     $saleOrderId = $item -> sale_order_id;
    //     return $saleOrderId;
    // }

    public function sale_order_items()
    {
        $item = $this -> items() -> first();
        $saleOrderId = $item -> sale_order_id;
        $saleOrderItems = collect([]);
        if ($saleOrderId) {
            $saleOrderItems = ErpSoItem::where('sale_order_id', $saleOrderId) -> with(['discount_ted', 'tax_ted']) -> with(['item' => function ($itemQuery) {
                $itemQuery -> with(['specifications', 'alternateUoms.uom', 'uom', 'hsn']);
            }]) -> get();
        }
        return $saleOrderItems;
    }

    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
    public function dynamic_fields()
    {
        return $this -> hasMany(ErpSiDynamicField::class, 'header_id');
    }
}
