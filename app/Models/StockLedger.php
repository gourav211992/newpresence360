<?php
namespace App\Models;

use App\Traits\DateFormatTrait;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLedger extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait, DefaultGroupCompanyOrg;

    protected $table = 'stock_ledger';

    // Define relationships
    public function attributes()
    {
        return $this->hasMany(StockLedgerItemAttribute::class, 'stock_ledger_id');
    }

    public function details()
    {
        return $this->hasMany(StockLedgerDetail::class, 'stock_ledger_id');
    }

    public function reservations()
    {
        return $this->hasMany(StockLedgerReservation::class, 'stock_ledger_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(StockLedger::class, 'utilized_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function store()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }

    public function location()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }

    public function rack()
    {
        return $this->belongsTo(ErpRack::class, 'rack_id');
    }

    public function shelf()
    {
        return $this->belongsTo(ErpShelf::class, 'shelf_id');
    }

    public function bin()
    {
        return $this->belongsTo(ErpBin::class, 'bin_id');
    }

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class, 'document_header_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function wipStation()
    {
        return $this->belongsTo(Station::class, 'wip_station_id');
    }

    public function mrnDetail()
    {
        return $this->belongsTo(MrnDetail::class, 'document_detail_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function item_attributes_array()
    {
        $attributes = $this->attributes()->where('stock_ledger_id', $this->id)->where('item_id', $this->item_id)->whereNull('deleted_at')->get();

        if ($attributes->isNotEmpty()) {
            $formattedAttributes = $attributes->map(function ($attribute) {
                return [
                    'attr_name' => (string) $attribute->attribute_name, // Convert to string
                    'attribute_name' => (string) optional(ErpAttributeGroup::find((int) $attribute->attribute_name))->name ?? '',
                    'attr_value' => (string) $attribute->attribute_value, // Convert to string
                    'attribute_value' => (string) optional(ErpAttribute::find((int) $attribute->attribute_value))->value ?? '',
                ];
            })->toArray();

            return json_encode($formattedAttributes); // Convert to JSON string
        }

        return json_encode([]); // Return an empty JSON array if no attributes exist
    }

    public function getSoNoAttribute()
    {
        $so = ErpSaleOrder::find($this->so_id);
        if ($so) {
            $soNo = $so->book_code . '-' . $so->document_number;
            return $soNo;
        }
        return null;
    }

}
