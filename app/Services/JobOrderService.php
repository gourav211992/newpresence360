<?php

namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Models\Bom;
use App\Models\BomDetail;
use App\Models\JobOrder\JoBomMapping;
use App\Models\JobOrder\JobOrder;
use App\Models\JobOrder\JobOrderTed;
use App\Models\JobOrder\JoItem;
use App\Models\JobOrder\JoItemAttribute;
use App\Models\JobOrder\JoProduct;
use App\Models\JobOrder\JoProductAttribute;
use App\Models\JobOrder\JoProductDelivery;
use App\Models\JobOrder\JoTerm;
use App\Models\PwoBomMapping;
use App\Models\PwoSoMapping;
use PHPUnit\TextUI\Configuration\Constant;

class JobOrderService
{
    # Job Order mapping to pwo so mapping
    public static function syncPwoJoMapping(array $component, $joProduct): void
    {
        $pwoSoMappingId = $component['pwo_so_mapping_id'] ?? null;
        $requestedQty = floatval($component['order_qty']) ?? 0;
        if (!$pwoSoMappingId || $requestedQty <= 0) {
            return;
        }
        $pwoSoMapping = PwoSoMapping::find($pwoSoMappingId);
        if (!$pwoSoMapping) {
            return;
        }
        $balanceQty = floatval($pwoSoMapping->qty) - floatval($pwoSoMapping->jo_qty);
        if ($balanceQty <= 0) {
            return;
        }
        $isAlreadyCounted = $joProduct->pwo_so_mapping_id == $pwoSoMappingId;
        if (!$isAlreadyCounted) {
            return;
        }
        $qtyToAdd = min(floatval($joProduct->order_qty), $balanceQty);
        if ($qtyToAdd > 0) {
            $pwoSoMapping->update([
                'jo_qty' => floatval($pwoSoMapping->jo_qty) + $qtyToAdd,
            ]);
        }
    }   

    # Save Job Item And Attribute
    public static function saveJoProductWithAttributes(array $poItem, array $component, ?int $jobOrderId): JoProduct
    {
        $itemHeaderExp = floatval($poItem['expense_amount']);
        $joProduct = JoProduct::find($component['jo_product_id'] ?? null) ?? new JoProduct;

        $isNewItem = false;
        if(isset($joProduct->item_id) && $joProduct->item_id) {
            $isNewItem = $joProduct->item_id != ($poItem['item_id'] ?? null);
        }
        $joProduct->pwo_so_mapping_id = $poItem['pwo_so_mapping_id'] ?? null;
        $joProduct->so_id = $poItem['so_id'] ?? null;
        $joProduct->jo_id = $jobOrderId;
        $joProduct->item_id = $poItem['item_id'];
        $joProduct->service_item_id = $poItem['service_item_id'];
        $joProduct->item_code = $poItem['item_code'];
        $joProduct->hsn_id = $poItem['hsn_id'];
        $joProduct->hsn_code = $poItem['hsn_code'];
        $joProduct->uom_id = $poItem['uom_id'];
        $joProduct->uom_code = $poItem['uom_code'];
        $joProduct->order_qty = $poItem['order_qty'];
        $joProduct->inventory_uom_id = $poItem['inventory_uom_id'];
        $joProduct->inventory_uom_code = $poItem['inventory_uom_code'];
        $joProduct->inventory_uom_qty = $poItem['inventory_uom_qty'];
        $joProduct->rate = $poItem['rate'];
        $joProduct->item_discount_amount = $poItem['item_discount_amount'];
        $joProduct->header_discount_amount = $poItem['header_discount_amount'];
        $joProduct->expense_amount = $itemHeaderExp;
        $joProduct->tax_amount = $poItem['tax_amount'];
        $joProduct->remarks = $poItem['remarks'];
        $joProduct->delivery_date = $poItem['delivery_date'];
        $joProduct->save();
        if ($isNewItem && $joProduct->id) {
            JoProductAttribute::where('jo_product_id', $joProduct->id)
                ->delete();
        }
        foreach ($joProduct->item->itemAttributes as $itemAttribute) {
            $groupId = $itemAttribute?->attribute_group_id;
            if (isset($component['attr_group_id'][$groupId])) {
                $attrData = $component['attr_group_id'][$groupId];
                $poAttr = JoProductAttribute::find($attrData['attr_id'] ?? null) ?? new JoProductAttribute;
                $poAttr->jo_id = $jobOrderId;
                $poAttr->jo_product_id = $joProduct->id;
                $poAttr->item_attribute_id = $itemAttribute->id;
                $poAttr->item_code = $component['item_code'] ?? null;
                $poAttr->attribute_name = $groupId;
                $poAttr->attribute_value = $attrData['attr_name'] ?? null;
                $poAttr->save();
            }
        }
        return $joProduct;
    }
    # Save Jo Item Delivery
    public static function saveJoProductDeliveries(JoProduct $joProduct, ?array $component, int $jobOrderId): void
    {
        // Case 1: Save from component deliveries
        if (isset($component['delivery']) && is_array($component['delivery'])) {
            foreach ($component['delivery'] as $delivery) {
                if (!empty($delivery['d_qty'])) {
                    $poItemDelivery = JoProductDelivery::find($delivery['id'] ?? null) ?? new JoProductDelivery;
                    $poItemDelivery->jo_id = $jobOrderId;
                    $poItemDelivery->jo_product_id = $joProduct->id;
                    $poItemDelivery->qty = floatval($delivery['d_qty']);
                    $poItemDelivery->delivery_date = $delivery['d_date'] ?? now();
                    $poItemDelivery->save();
                }
            }
        }
        // Case 2: If no delivery records exist for this item, fallback to default
        if ($joProduct->productDelivery()->count() < 1) {
            $poItemDelivery = new JoProductDelivery;
            $poItemDelivery->jo_id = $jobOrderId;
            $poItemDelivery->jo_product_id = $joProduct->id;
            $poItemDelivery->qty = floatval($joProduct->order_qty ?? 0.00);
            $poItemDelivery->delivery_date = $joProduct->delivery_date ?? now();
            $poItemDelivery->save();
        }
    }
    # Save Item Level Discount
    public static function saveJoProductDiscounts(JoProduct $joProduct, array $component, array $poItem, int $jobOrderId): float
    {
        $totalItemLevelDiscValue = 0.00;
        if (isset($component['discounts']) && is_array($component['discounts'])) {
            foreach ($component['discounts'] as $dis) {
                if (!empty($dis['dis_amount'])) {
                    $ted = JobOrderTed::find($dis['id'] ?? null) ?? new JobOrderTed;
                    $ted->jo_id = $jobOrderId;
                    $ted->jo_product_id = $joProduct->id;
                    $ted->ted_type = 'Discount';
                    $ted->ted_level = 'D';
                    $ted->ted_id = $dis['ted_id'] ?? null;
                    $ted->ted_name = $dis['dis_name'];
                    $ted->assessment_amount = $poItem['item_value'] ?? 0;
                    $ted->ted_perc = $dis['dis_perc'] ?? 0.00;
                    $ted->ted_amount = $dis['dis_amount'] ?? 0.00;
                    $ted->applicable_type = 'Deduction';
                    $ted->save();
                    $totalItemLevelDiscValue += $ted->ted_amount;
                }
            }
        }
        return $totalItemLevelDiscValue;
    }
    # Save Item Taxes
    public static function saveJoProductTaxes(JoProduct $joProduct, array $component, array $poItem, int $jobOrderId): void
    {
        if (isset($component['taxes']) && is_array($component['taxes'])) {
            foreach ($component['taxes'] as $tax) {
                if (!empty($tax['t_value'])) {
                    $ted = JobOrderTed::find($tax['id'] ?? null) ?? new JobOrderTed;
                    $ted->jo_id = $jobOrderId;
                    $ted->jo_product_id = $joProduct->id;
                    $ted->ted_type = 'Tax';
                    $ted->ted_level = 'D';
                    $ted->ted_id = $tax['t_d_id'] ?? null;
                    $ted->ted_name = $tax['t_type'] ?? null;
                    $ted->assessment_amount = ($poItem['item_value'] ?? 0)
                                            - ($poItem['item_discount_amount'] ?? 0)
                                            - ($poItem['header_discount_amount'] ?? 0);
                    $ted->ted_perc = $tax['t_perc'] ?? 0.00;
                    $ted->ted_amount = $tax['t_value'] ?? 0.00;
                    $ted->applicable_type = $tax['applicability_type'] ?? 'Collection';
                    $ted->save();
                }
            }
        }
    }
    # Save Header Level Discount
    public static function saveHeaderLevelDiscounts(array $discSummary, float $itemTotalValue, float $itemTotalDiscount, int $jobOrderId): void
    {
        foreach ($discSummary as $dis) {
            if (!empty($dis['d_amnt'])) {
                $ted = JobOrderTed::find($dis['d_id'] ?? null) ?? new JobOrderTed;
                $ted->jo_id = $jobOrderId;
                $ted->jo_product_id = null;
                $ted->ted_type = 'Discount';
                $ted->ted_level = 'H';
                $ted->ted_id = $dis['ted_d_id'] ?? null;
                $ted->ted_name = $dis['d_name'] ?? null;
                $ted->assessment_amount = $itemTotalValue - $itemTotalDiscount;
                $ted->ted_perc = $dis['d_perc'] ?? 0.00;
                $ted->ted_amount = $dis['d_amnt'] ?? 0.00;
                $ted->applicable_type = 'Deduction';
                $ted->save();
            }
        }
    }
    # Save Header Level Exp
    public static function saveHeaderLevelExpenses(array $expSummary, float $itemTotalValue, float $itemTotalDiscount, float $itemTotalHeaderDiscount, float $totalTax, int $jobOrderId): void
    {
        $totalAfterTax = $itemTotalValue - $itemTotalDiscount - $itemTotalHeaderDiscount + $totalTax;
        foreach ($expSummary as $exp) {
            if (!empty($exp['e_amnt'])) {
                $ted = JobOrderTed::find($exp['e_id'] ?? null) ?? new JobOrderTed;
                $ted->jo_id = $jobOrderId;
                $ted->jo_product_id = null;
                $ted->ted_type = 'Expense';
                $ted->ted_level = 'H';
                $ted->ted_id = $exp['ted_e_id'] ?? null;
                $ted->ted_name = $exp['e_name'] ?? null;
                $ted->assessment_amount = $totalAfterTax;
                $ted->ted_perc = $exp['e_perc'] ?? 0.00;
                $ted->ted_amount = $exp['e_amnt'] ?? 0.00;
                $ted->applicable_type = 'Collection';
                $ted->save();
            }
        }
    }
    # Save Term & Condition
    public static function saveJobOrderTerms($po, $request): void
    {
        if (!empty($request->term_id)) {
            foreach ($request->term_id as $index => $term_id) {
                $termCode = $request->term_code[$index] ?? null;
                $description = $request->description[$index] ?? null;
                $existingTerm = $po->termsConditions()
                    ->where('term_id', $term_id)
                    ->where('jo_id', $po->id)
                    ->first();
                if ($existingTerm) {
                    $existingTerm->term_code = $termCode;
                    $existingTerm->remarks = $description;
                    $existingTerm->save();
                } else {
                    $poTerm = new JoTerm;
                    $poTerm->jo_id = $po->id;
                    $poTerm->term_id = $term_id;
                    $poTerm->term_code = $termCode;
                    $poTerm->remarks = $description;
                    $poTerm->save();
                }
            }
        }
    }
    # Address Details
    public static function saveAddressDetails($po, $type, $sourceAddress): void
    {
        if (!$sourceAddress) return;
        $relationMethod = match ($type) {
            'billing' => 'bill_address_details',
            'shipping' => 'ship_address_details',
            'location' => 'store_address',
        };
        $addressModel = $po->{$relationMethod}()->firstOrNew(['type' => $type]);
        $addressModel->fill([
            'type' => $type,
            'address' => $sourceAddress->address ?? null,
            'country_id' => $sourceAddress->country_id ?? null,
            'state_id' => $sourceAddress->state_id ?? null,
            'city_id' => $sourceAddress->city_id ?? null,
            'pincode' => $sourceAddress->pincode ?? $sourceAddress->postal_code ?? null,
            'phone' => $sourceAddress->phone ?? $sourceAddress->mobile ?? null,
            'fax_number' => $sourceAddress->fax_number ?? null,
        ]);
        $addressModel->save();
    }
    // normalizeBomAttributes
    public static function normalizeBomAttributes($attributes): array
    {
        if ($attributes instanceof \Illuminate\Support\Collection) {
            return $attributes->map(function ($attr) {
                return [
                    'attribute_id'    => $attr->item_attribute_id,
                    'attribute_name'  => $attr->attribute_name,
                    'attribute_value' => $attr->attribute_value,
                ];
            })->toArray();
        } elseif (is_array($attributes)) {
            return $attributes;
        }
        return [];
    }

    # Job Order Bom Mapping
    public static function mapJobOrderBom(JobOrder $po, JoProduct $joProduct): void
    {
        if (strtolower($po->job_order_type) !== strtolower(ConstantHelper::TYPE_SUBCONTRACTING)) {
            return;
        }
        $bomDetails = PwoBomMapping::where('pwo_mapping_id', $joProduct?->pwo_so_mapping_id)->get();
        if ($bomDetails->isEmpty()) {
            $bom = Bom::withDefaultGroupCompanyOrg()
                ->whereIn('production_type', ['Job Work'])
                ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
                ->where('item_id', $joProduct?->item_id)
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                ->first();
            if (!$bom) return;
            $bomDetails = BomDetail::with('attributes')
                ->where('bom_id', $bom->id)
                ->get();
        }
        
        $insertData = [];
        foreach ($bomDetails as $bomDetail) {
            $bomQty = 0;
            if ($bomDetail instanceof PwoBomMapping) {
                $bomQty = floatval($bomDetail?->bomDetail?->qty) ?? 0;
            }
            if ($bomDetail instanceof BomDetail) {
                $bomQty = floatval($bomDetail?->qty) ?? 0;
            }
            $attributes = self::normalizeBomAttributes($bomDetail->attributes);
            $insertData[] = [
                'jo_id'           => $po->id,
                'jo_product_id'   => $joProduct->id,
                'so_id'           => $joProduct->so_id,
                'bom_id'          => $bomDetail->bom_id,
                'bom_detail_id'   => $bomDetail->id,
                'item_id'         => $bomDetail->item_id,
                'item_code'       => $bomDetail->item_code,
                'attributes'      => json_encode($attributes),
                'uom_id'          => $bomDetail->uom_id,
                'bom_qty'         => (float) $bomQty,
                'qty'             => (float) $joProduct->inventory_uom_qty * (float) $bomQty,
                'station_id'      => $bomDetail->station_id,
                'section_id'      => $bomDetail->section_id,
                'sub_section_id'  => $bomDetail->sub_section_id,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }
        if (!empty($insertData)) {
            JoBomMapping::insert($insertData);
        }
    }
    # Job Order Bom Mapping
    public static function saveJoItems(JobOrder $po): void
    {
        if (strtolower($po->job_order_type) !== strtolower(ConstantHelper::TYPE_SUBCONTRACTING)) {
            return;
        }
        $groupedDatas = JoBomMapping::selectRaw('jo_id, so_id, station_id, bom_detail_id, item_id, item_code, uom_id, rm_type, attributes, SUM(qty) as total_qty')
                        ->where('jo_id', $po->id)
                        ->groupBy('jo_id', 'so_id', 'station_id', 'bom_detail_id', 'item_id', 'item_code', 'uom_id', 'rm_type', 'attributes')
                        ->get();
        foreach($groupedDatas as $groupedData) {
            # Mo Item Save                    
            $moItem = new JoItem;
            $moItem->jo_id = $po->id;
            $moItem->so_id = $groupedData->so_id ?? null;
            $moItem->bom_detail_id = $groupedData->bom_detail_id;
            $moItem->station_id = $groupedData->station_id;
            $moItem->item_id = $groupedData->item_id;
            $moItem->item_code = $groupedData->item_code;
            $moItem->uom_id = $groupedData->uom_id;
            $moItem->rm_type = $groupedData->rm_type;
            $moItem->qty = $groupedData->total_qty;
            $moItem->inventory_uom_id = $groupedData?->item?->uom_id;
            $moItem->inventory_uom_code = $groupedData?->item?->uom?->name;
            $moItem->inventory_uom_qty = $groupedData->total_qty;
            $moItem->save();
            # Mo Item Attribute Save
            $moItemAttributes = $groupedData->attributes;
            foreach($moItemAttributes as $moItemAttribute) {
                $moItemAttr = new JoItemAttribute;
                $moItemAttr->jo_id = $po->id;
                $moItemAttr->jo_item_id = $moItem->id;
                $moItemAttr->item_id = $groupedData->item_id;
                $moItemAttr->item_code = $groupedData->item_code;
                $moItemAttr->item_attribute_id = $moItemAttribute['attribute_id'] ?? null;
                $moItemAttr->attribute_name = $moItemAttribute['attribute_name'] ?? null;
                $moItemAttr->attribute_value = $moItemAttribute['attribute_value'] ?? null;
                $moItemAttr->save();
            }
        }
    }
}
