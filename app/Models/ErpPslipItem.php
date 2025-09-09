<?php

namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\ItemHelper;
use App\Models\Scrap\ErpScrap;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpPslipItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'erp_scrap_id',
        'pslip_id',
        'mo_product_id',
        'item_id',
        'station_id',
        'so_id',
        'so_item_id',
        'item_code',
        'item_name',
        // 'hsn_id',
        // 'hsn_code',
        'uom_id',
        'uom_code',
        'store_id',
        'sub_store_id',
        'qty',
        'inventory_uom_id',
        // 'inventory_uom_code',
        'inventory_uom_qty',
        'rate',
        'customer_id',
        // 'item_discount_amount',
        // 'header_discount_amount',
        // 'item_expense_amount',
        // 'header_expense_amount',
        // 'tax_amount',
        // 'total_item_amount',
        'remarks',
        'accepted_qty',
        'subprime_qty',
        'rejected_qty',
        'wip_qty',
        'machine_id',
        'cycle_count',
        'station_line_id',
        'supervisor_name'
    ];

    public $referencingRelationships = [
        'item' => 'item_id',
        'attributes' => 'mi_item_id',
        'uom' => 'uom_id',
        'hsn' => 'hsn_id',
        'inventoryUom' => 'inventory_uom_id'
    ];

    protected $hidden = ['deleted_at'];
    protected $with = ['mo_product.mo'];
    protected $casts = [
        'machine_id' => 'array'
    ];
    public function getItemValueAttribute()
    {
        return $this->qty * $this->rate;
    }

    public function scrap()
    {
        return $this->belongsTo(ErpScrap::class, 'erp_scrap_id', 'id');
    }

    public function pslip()
    {
        return $this->belongsTo(ErpProductionSlip::class, 'pslip_id', 'id');
    }

    public function mo_product()
    {
        return $this->belongsTo(MoProduct::class, 'mo_product_id', 'id');
    }

    public function machine()
    {
        return $this->belongsTo(ErpMachine::class, 'machine_id', 'id');
    }

    public function consumptions()
    {
        return $this->hasMany(PslipBomConsumption::class, 'pslip_item_id', 'id');
    }

    public function getMoAttribute()
    {
        return $this->mo_product?->mo;
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }
    public function so()
    {
        return $this->belongsTo(ErpSaleOrder::class, 'so_id', 'id');
    }
    public function so_item()
    {
        return $this->belongsTo(ErpSoItem::class, 'so_item_id', 'id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function item_attributes()
    {
        return $this->belongsTo(ErpPslipItemAttribute::class, 'pslip_item_id');
    }

    public function attributes()
    {
        return $this->hasMany(ErpPslipItemAttribute::class, 'pslip_item_id');
    }

    public function checklists()
    {
        return $this->hasMany(InspChecklist::class, 'detail_id')
            ->whereType(ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS);
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }

    public function inventoryUom()
    {
        return $this->belongsTo(Unit::class, 'inventory_uom_id');
    }

    public function item_attributes_array()
    {
        $itemId = $this->getAttribute('item_id');
        if (isset($itemId)) {
            $itemAttributes = ItemAttribute::where('item_id', $this->item_id)->get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $existingAttribute = ErpPslipItemAttribute::where('pslip_item_id', $this->getAttribute('id'))->where('item_attribute_id', $attribute->id)->first();
            if (!isset($existingAttribute)) {
                continue;
            }
            $attributesArray = array();
            $attribute_ids = $attribute->attribute_id ? ($attribute->attribute_id) : [];
            $attribute->group_name = $attribute->group?->name;
            foreach ($attribute_ids as $attributeValue) {
                $attributeValueData = ErpAttribute::where('id', $attributeValue)->select('id', 'value')->where('status', 'active')->first();
                if (isset($attributeValueData)) {
                    $isSelected = ErpPslipItemAttribute::where('pslip_item_id', $this->getAttribute('id'))->where('item_attribute_id', $attribute->id)->where('attribute_value', $attributeValueData->value)->first();
                    $attributeValueData->selected = $isSelected ? true : false;
                    array_push($attributesArray, $attributeValueData);
                }
            }
            $attribute->values_data = $attributesArray;
            $attribute = $attribute->only(['id', 'group_name', 'values_data', 'attribute_group_id']);
            array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id']]);
        }
        $processedData = collect($processedData);
        return $processedData;
    }
    public function hsn()
    {
        return $this->belongsTo(Hsn::class);
    }
    public function to_item_locations()
    {
        return $this->hasMany(ErpPslipItemLocation::class, 'pslip_item_id', 'id');
    }
    public function header()
    {
        return $this->belongsTo(ErpProductionSlip::class, 'pslip_id');
    }
    public function store()
    {
        return $this->belongsTo(ErpStore::class, 'store_id');
    }
    public function sub_store()
    {
        return $this->belongsTo(ErpSubStore::class, 'sub_store_id');
    }
    public function bundles()
    {
        return $this->hasMany(ErpPslipItemDetail::class, 'pslip_item_id');
    }
    public function getStockBalanceQty($storeId = null)
    {
        $itemId = $this->getAttribute('item_id');
        $selectedAttributeIds = [];
        $itemAttributes = $this->item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($itemId, $selectedAttributeIds, $storeId, null, null, null);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        // $stockBalanceQty = $this -> getAttribute('inventory_uom_qty');
        $stockBalanceQty = ItemHelper::convertToAltUom($this->getAttribute(('item_id')), $this->getAttribute('uom_id'), $stockBalanceQty);
        return $stockBalanceQty;
        // return $this -> getAttribute('order_qty');
    }

    public function renderItemAttributesUI(int $rowIndex, int $maxCharLimit = 15): string
    {
        $itemId = $this->getAttribute('item_id');
        if (!$itemId) {
            return $this->defaultAttrBtn($rowIndex);
        }

        $itemAttributes = ItemAttribute::with('group:id,name')
            ->where('item_id', $itemId)
            ->get(['id', 'item_id', 'attribute_id', 'attribute_group_id']);

        if ($itemAttributes->isEmpty()) {
            return $this->defaultAttrBtn($rowIndex);
        }

        $pslipAttrs = ErpPslipItemAttribute::where('pslip_item_id', $this->getAttribute('id'))
            ->get(['item_attribute_id', 'attribute_value'])
            ->groupBy('item_attribute_id');

        $allAttrIds = $itemAttributes->pluck('attribute_id')->flatten()->unique()->toArray();

        $attrValues = ErpAttribute::whereIn('id', $allAttrIds)
            ->where('status', 'active')
            ->get(['id', 'value'])
            ->keyBy('id');

        $attributeUI   = '<div style="white-space:nowrap; cursor:pointer;">';
        $charUsed      = 0;
        $selectedCount = 0;
        $stopAdding    = false;

        foreach ($itemAttributes as $attribute) {
            $selectedValues = $pslipAttrs->get($attribute->id)?->pluck('attribute_value')->toArray() ?? [];
            if (empty($selectedValues)) {
                continue;
            }

            $groupName   = $attribute->group?->name ?? '';
            $selectedVal = '';

            foreach ((array)$attribute->attribute_id as $attrId) {
                if (isset($attrValues[$attrId]) && in_array($attrValues[$attrId]->value, $selectedValues, true)) {
                    $selectedVal = $attrValues[$attrId]->value;
                    break;
                }
            }

            if (!empty($selectedVal)) {
                $selectedCount++;
            }

            $groupText = $groupName . ': ' . $selectedVal;
            $length    = strlen($groupText);

            if ($stopAdding) {
                continue;
            }

            if ($charUsed + $length <= $maxCharLimit) {
                $attributeUI .= sprintf(
                    '<span class="badge rounded-pill badge-light-primary">
                    <strong>%s</strong>: %s
                </span>',
                    e($groupName),
                    e($selectedVal)
                );
                $charUsed += $length;
            } else {
                $remain = $maxCharLimit - $charUsed;

                if ($remain >= 3) {
                    $attributeUI .= sprintf(
                        '<span class="badge rounded-pill badge-light-primary">
                        <strong>%s..</strong>
                    </span>',
                        e(substr($groupName, 0, $remain - 1))
                    );
                } else {
                    $attributeUI .= '<i class="ml-2 fa-solid fa-ellipsis-vertical"></i>';
                }

                $stopAdding = true;
            }
        }

        $attributeUI .= '</div>';

        return $selectedCount ? $attributeUI : $this->defaultAttrBtn($rowIndex);
    }

    /**
     * Default button if no attributes selected
     */
    private function defaultAttrBtn(int $rowIndex): string
    {
        return <<<HTML
        <button id="attribute_button_{$rowIndex}" type="button"
            class="btn p-25 btn-sm btn-outline-secondary"
            style="font-size: 10px">Attributes</button>
        <input type="hidden" name="attribute_value_{$rowIndex}" />
    HTML;
    }

    public function getAvlStock($storeId, $subStoreId = null, $stationId = null)
    {
        $selectedAttributeIds = [];
        $itemAttributes = $this -> item_attributes_array();
        foreach ($itemAttributes as $itemAttr) {
            foreach ($itemAttr['values_data'] as $valueData) {
                if ($valueData['selected']) {
                    array_push($selectedAttributeIds, $valueData['id']);
                }
            }
        }
        $stocks = InventoryHelper::totalInventoryAndStock($this -> item_id, $selectedAttributeIds,$this -> uom_id,$storeId, $subStoreId, null, $stationId);
        $stockBalanceQty = 0;
        if (isset($stocks) && isset($stocks['confirmedStocks'])) {
            $stockBalanceQty = $stocks['confirmedStocks'];
        }
        // return min($stockBalanceQty, $this -> qty);
        return $stockBalanceQty;
    }

    public function getMiAcceptedBalanceQtyAttribute()
    {
        $currentQty = $this -> getAttribute('accepted_qty');
        $miQty = $this -> getAttribute('mi_accepted_qty');
        return $currentQty - $miQty;
    }
    public function getMiSubPrimeBalanceQtyAttribute()
    {
        $currentQty = $this -> getAttribute('subprime_qty');
        $miQty = $this -> getAttribute('mi_subprime_qty');
        return $currentQty - $miQty;
    }
}
