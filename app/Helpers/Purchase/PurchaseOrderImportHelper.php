<?php

namespace App\Helpers\Purchase;


use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Helpers\TaxHelper;
use App\Models\Attribute;
use App\Models\Book;
use App\Models\Item;
use App\Models\Organization;
use App\Models\PoItem;
use App\Models\Vendor;
use App\Models\ErpStore;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderTed;
use App\Models\PoItemAttribute;
use App\Models\PoItemDelivery;
use App\Models\ErpPoDynamicField;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PurchaseOrderImportHelper
{
    public static function getPoImports(): array
    {
        return [
            'v1' => asset('templates/PurchaseOrderImport.xlsx'),
        ];
    }

    public static function getPoImportHeaders(): array
    {
        return [
            'v1' => "
                <th class='no-wrap'>Order No</th>
                <th class='no-wrap'>Order Date</th>
                <th class='no-wrap'>Vendor</th>
                <th class='no-wrap'>Consignee</th>
                <th class='no-wrap'>Item Code</th>
                <th class='no-wrap'>UOM</th>
                <th class='numeric-alignment no-wrap'>Total Qty</th>
                <th class='numeric-alignment'>Rate</th>
                <th class='no-wrap'>Delivery Date</th>
            ",
        ];
    }

    /**
     * Save v2 imported data as Purchase Orders
     */
    public static function v2ImportDataSave(Collection $data, int $bookId, int $locationId, string $procurementType ,$user, string $document_status): array
    {
        $successfullOrders = 0;

        // Organization, Group, Company
        $organization   = Organization::find($user->organization_id);
        $organizationId = $organization?->id;
        $groupId        = $organization?->group_id;
        $companyId      = $organization?->company_id;

        $book           = Book::find($bookId);
        $location       = ErpStore::find($locationId);
        $companyCountryId = $location?->address?->country_id ?? null;
        $companyStateId   = $location?->address?->state_id ?? null;
        $locationAddress  = $location?->address;

        if (!$locationAddress) {
            return [
                'message' => 'Location Address is not specified',
                'status'  => 422
            ];
        }

        $addedOrders     = [];
        $createdOrderIds = [];

        foreach ($data as $uploadData) {
            // Skip rows with existing errors
            if (!empty($uploadData->reason)) {
                continue;
            }

            $errors        = [];
            $currentOrder  = $uploadData->order_no;

            if (!in_array($currentOrder, $addedOrders)) {
                // Generate document number
                $numberPatternData = Helper::generateDocumentNumberNew($bookId, $uploadData->document_date);
                if (!$numberPatternData) {
                    return [
                        'message' => "Invalid Book",
                        'status'  => 422
                    ];
                }

                $document_number = $numberPatternData['document_number'] ?: $uploadData->order_no;

                $regeneratedDocExist = PurchaseOrder::withDefaultGroupCompanyOrg()
                    ->where('book_id', $bookId)
                    ->where('document_number', $document_number)
                    ->first();

                if ($regeneratedDocExist) {
                    $errors[]             = ConstantHelper::DUPLICATE_DOCUMENT_NUMBER;
                    $uploadData->reason   = json_encode($errors);
                    $uploadData->save();
                    continue;
                }

                // Vendor check
                $vendor = Vendor::find($uploadData->vendor_id);
                if (!$vendor) {
                    $errors[]           = 'Vendor not found';
                    $uploadData->reason = json_encode($errors);
                    $uploadData->save();
                    continue;
                }

                // Vendor details
                $vendorPhoneNo = $vendor->mobile ?? null;
                $vendorEmail   = $vendor->email ?? null;
                $vendorGSTIN   = $vendor->compliances?->gstin_no ?? null;

                // Currency
                $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($vendor->currency_id, $uploadData->document_date);
                if ($currencyExchangeData['status'] == false) {
                    $errors[]           = $currencyExchangeData['message'];
                    $uploadData->reason = json_encode($errors);
                    $uploadData->save();
                    continue;
                }

                // Create PO header
                $purchaseOrder = PurchaseOrder::create([
                    'organization_id'   => $organizationId,
                    'group_id'          => $groupId,
                    'company_id'        => $companyId,
                    'book_id'           => $bookId,
                    'book_code'         => $book?->book_code,
                    'document_type'     => $book->document_type ?? $book->book_code ?? 'PO',
                    'document_number'   => $document_number,
                    'doc_number_type'   => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix'        => $numberPatternData['prefix'],
                    'doc_suffix'        => $numberPatternData['suffix'],
                    'doc_no'            => $numberPatternData['doc_no'],
                    'document_date'     => $uploadData->document_date,
                    'procurement_type'  => $procurementType ?? null,
                    'revision_number'   => 0,
                    'revision_date'     => null,
                    'reference_number'  => $uploadData->order_no,
                    'store_id'          => $locationId,
                    'store_code'        => $location?->store_name,

                    'vendor_id'         => $vendor->id,
                    'vendor_email'      => $vendorEmail,
                    'vendor_phone_no'   => $vendorPhoneNo,
                    'vendor_gstin'      => $vendorGSTIN,
                    'vendor_code'       => $vendor->company_name,

                    'consignee_name'    => $uploadData->consignee_name,

                    'currency_id'       => $vendor->currency_id,
                    'currency_code'     => $vendor->currency?->short_name,
                    'payment_term_id'   => $vendor->payment_terms_id,
                    'payment_term_code' => $vendor->paymentTerm?->alias,

                    'document_status'   => ConstantHelper::DRAFT,
                    'approval_level'    => 1,
                    'remarks'           => '',

                    'org_currency_id'       => $currencyExchangeData['data']['org_currency_id'],
                    'org_currency_code'     => $currencyExchangeData['data']['org_currency_code'],
                    'org_currency_exg_rate' => $currencyExchangeData['data']['org_currency_exg_rate'],
                    'comp_currency_id'      => $currencyExchangeData['data']['comp_currency_id'],
                    'comp_currency_code'    => $currencyExchangeData['data']['comp_currency_code'],
                    'comp_currency_exg_rate'=> $currencyExchangeData['data']['comp_currency_exg_rate'],
                    'group_currency_id'     => $currencyExchangeData['data']['group_currency_id'],
                    'group_currency_code'   => $currencyExchangeData['data']['group_currency_code'],
                    'group_currency_exg_rate'=> $currencyExchangeData['data']['group_currency_exg_rate'],

                    'total_item_value'      => 0,
                    'total_discount_value'  => 0,
                    'total_tax_value'       => 0,
                    'total_expense_value'   => 0,
                ]);

                // Vendor addresses
                $vendorBillingAddress  = $vendor->addresses()->whereIn('type', ['billing','both'])->first();
                $vendorShippingAddress = $vendor->addresses()->whereIn('type', ['shipping','both'])->first();
                if (!$vendorBillingAddress || !$vendorShippingAddress) {
                    $errors[]           = "Vendor addresses not properly setup";
                    $uploadData->reason = json_encode($errors);
                    $uploadData->save();
                    continue;
                }

                // Save addresses
                $billingAddress  = $purchaseOrder->bill_address_details()->create($vendorBillingAddress->toArray());
                $shippingAddress = $purchaseOrder->ship_address_details()->create($vendorShippingAddress->toArray());
                $locationAddress = $purchaseOrder->store_address()->create($locationAddress->toArray());
                $purchaseOrder->billing_address  = $billingAddress->id;
                $purchaseOrder->shipping_address = $shippingAddress->id;
                $purchaseOrder->save();

                // Dynamic fields (default null values)
                foreach ($book->dynamic_fields as $bookDynamicField) {
                    $dynamicField = $bookDynamicField->dynamic_field;
                    foreach ($dynamicField->details as $dynamicFieldDetail) {
                        ErpPoDynamicField::create([
                            'header_id'             => $purchaseOrder->id,
                            'dynamic_field_id'      => $dynamicField->id,
                            'dynamic_field_detail_id'=> $dynamicFieldDetail->id,
                            'name'                  => $dynamicFieldDetail->name,
                            'value'                 => $uploadData->{$dynamicFieldDetail->name} ?? null
                        ]);
                    }
                }

                $addedOrders[] = $uploadData->order_no;
                $createdOrderIds[$uploadData->order_no] = $purchaseOrder;
            }

            // Items
            $poHeader = $createdOrderIds[$uploadData->order_no] ?? null;
            if (!$poHeader) continue;

            $item = Item::find($uploadData->item_id);
            if (!$item) {
                $errors[]           = 'Item not found';
                $uploadData->reason = json_encode($errors);
                $uploadData->save();
                continue;
            }

            $attributesArray = $uploadData->attributes ?? [];

            if (!isset($uploadData->uom_id)) {
                $uploadData->uom_id   = $item->uom_id;
                $uploadData->uom_code = $item->uom?->name;
            }

            if (!isset($uploadData->rate)) {
                $errors[]           = "Rate not specified";
                $uploadData->reason = json_encode($errors);
                $uploadData->save();
                continue;
            }

            if (isset($uploadData->qty) && $uploadData->qty > 0) {
                $itemValue = $uploadData->qty * $uploadData->rate;
                $itemTax   = 0;

                $taxDetails = TaxHelper::calculateTax(
                    $item->hsn_id,
                    $uploadData->rate,
                    $companyCountryId,
                    $companyStateId,
                    $shippingAddress->country_id ?? null,
                    $shippingAddress->state_id ?? null,
                    'purchase'
                );

                foreach ($taxDetails ?? [] as $taxDetail) {
                    $itemTax += ($taxDetail['tax_percentage'] / 100) * $itemValue;
                }

                $inventoryUomQty = ItemHelper::convertToBaseUom($item->id, $uploadData->uom_id, $uploadData->qty);

                $poItem = PoItem::create([
                    'purchase_order_id' => $poHeader->id,
                    'item_id'           => $item->id,
                    'item_code'         => $item->item_code,
                    'item_name'         => $item->item_name,
                    'hsn_id'            => $item->hsn_id,
                    'hsn_code'          => $item->hsn?->code,
                    'uom_id'            => $uploadData->uom_id,
                    'uom_code'          => $uploadData->uom_code,
                    'order_qty'         => $uploadData->qty,
                    'received_qty'      => 0,
                    'inventory_uom_id'  => $item->uom_id,
                    'inventory_uom_code'=> $item->uom?->name,
                    'inventory_uom_qty' => $inventoryUomQty,
                    'rate'              => $uploadData->rate,
                    'delivery_date'     => $uploadData->delivery_date ?? Carbon::now()->format('Y-m-d'),
                    'tax_amount'        => $itemTax,
                    'total_item_amount' => $itemValue + $itemTax
                ]);

                // TED rows
                foreach ($taxDetails ?? [] as $taxDetail) {
                    PurchaseOrderTed::create([
                        'purchase_order_id' => $poHeader->id,
                        'po_item_id'        => $poItem->id,
                        'ted_type'          => 'Tax',
                        'ted_level'         => 'D',
                        'ted_id'            => $taxDetail['id'],
                        'ted_group_code'    => $taxDetail['tax_group'],
                        'ted_name'          => $taxDetail['tax_type'],
                        'assessment_amount' => $itemValue,
                        'ted_percentage'    => $taxDetail['tax_percentage'],
                        'ted_amount'        => ($taxDetail['tax_percentage'] / 100) * $itemValue,
                        'applicable_type'   => 'Collection'
                    ]);
                }

                // Attributes
                foreach ($attributesArray as $itemAttr) {
                    PoItemAttribute::create([
                        'purchase_order_id'  => $poHeader->id,
                        'po_item_id'         => $poItem->id,
                        'item_attribute_id'  => $itemAttr['item_attribute_id'],
                        'item_code'          => $poItem->item_code,
                        'attribute_name'     => $itemAttr['attribute_name'],
                        'attr_name'          => $itemAttr['attr_name'],
                        'attribute_value'    => $itemAttr['attribute_value'],
                        'attr_value'         => $itemAttr['attr_value'],
                    ]);
                }

                // Delivery
                PoItemDelivery::create([
                    'purchase_order_id' => $poHeader->id,
                    'po_item_id'        => $poItem->id,
                    'qty'               => $uploadData->qty,
                    'received_qty'      => 0,
                    'delivery_date'     => $uploadData->delivery_date ?? Carbon::now()->format('Y-m-d')
                ]);
            }
        }

        // Finalize
        foreach ($createdOrderIds as $createdOrder) {
            $successfullOrders++;

            $items           = PoItem::where('purchase_order_id', $createdOrder->id)->get();
            $totalItemTax    = $items->sum('tax_amount');
            $totalItemValue = $items->sum(function ($item) {
                return $item->order_qty * $item->rate;
            });

            $po = PurchaseOrder::find($createdOrder->id);
            $po->total_tax_value   = $totalItemTax;
            // $po->total_amount      = $totalItemValue;
            $po->total_item_value  = $totalItemValue;
            $po->save();

            if ($document_status === ConstantHelper::SUBMITTED) {
                Helper::approveDocument(
                    $po->book_id,
                    $po->id,
                    $po->revision_number ?? 0,
                    $po->remarks,
                    [],
                    $po->approval_level,
                    'submit',
                    $po->total_amount ?? 0,
                    get_class($po)
                );

                $po->document_status = ConstantHelper::SUBMITTED;
                $po->save();
            }
        }

        // ✅ Finalize and return result
        if ($successfullOrders) {
            return [
                'message' => "$successfullOrders Purchase Order(s) imported successfully",
                'status'  => 200,
                'errors'  => [] // no errors on success
            ];
        }

        // Collect all failed rows with reasons
        $failedErrors = $data
            ->filter(fn($row) => isset($row->reason) && !empty($row->reason))
            ->map(function ($row) {
                return [
                    'order_no' => $row->order_no ?? null,
                    'reason'   => is_string($row->reason) ? json_decode($row->reason, true) : $row->reason,
                ];
            })
            ->values()
            ->toArray();

        return [
            'message' => "Purchase Order Import failed due to multiple errors. Please check the uploaded file again. " 
                        . json_encode($failedErrors),  // ✅ safe string format
            'status'  => 422,
            'errors'  => $failedErrors
        ];

    }


    /**
     * Build Valid/Invalid UI rows for header-level review
     * - v1 kept for compatibility (renamed field labels to Vendor where applicable)
     * - v2 uses single QTY and attributes badges
     */
    public static function generateValidInvalidUi(string $version, Collection $uploadsData) : array
    {
        $successRecords = 0;
        $failedRecords  = 0;
        $validUI = "";
        $invalidUI = "";

        if ($version == "v1") {
            foreach ($uploadsData as $uploadData) {
                $totalQty = 0;
                for ($i = 1; $i <= 14; $i++) {
                    $totalQty += $uploadData->qty;
                }

                $orderNo      = $uploadData->order_no ?? "";
                $docDate      = Carbon::parse($uploadData->document_date)->format("d-m-Y");
                $vendorCode   = $uploadData->vendor_code ?? "";
                $consignee    = $uploadData->consignee_name ?? "";
                $itemCode     = $uploadData->item_code ?? "";
                $uomCode      = $uploadData->uom_code ?? "";
                $rate         = $uploadData->rate ?? 0;
                $deliveryDate = Carbon::parse($uploadData->delivery_date)->format("d-m-Y");

                if ($uploadData->reason && count($uploadData->reason) > 0) {
                    $failedRecords += 1;
                    $errors = "";
                    foreach ($uploadData->reason as $errIndex => $errorReason) {
                        $errors .= ($errIndex == 0 ? $errorReason : ", " . $errorReason);
                    }
                    $invalidUI .= "
                        <tr>
                        <td class='no-wrap'>$orderNo</td>
                        <td class='no-wrap'>$docDate</td>
                        <td class='no-wrap'>$vendorCode</td>
                        <td class='no-wrap'>$consignee</td>
                        <td class='no-wrap'>$itemCode</td>
                        <td class='no-wrap'>$uomCode</td>
                        <td class='numeric-alignment'>$totalQty</td>
                        <td class='numeric-alignment'>$rate</td>
                        <td class='no-wrap'>$deliveryDate</td>
                        <td class='no-wrap text-danger'>$errors</td>
                        </tr>
                    ";
                } else {
                    $successRecords += 1;
                    $validUI .= "
                        <tr>
                        <td class='no-wrap'>$orderNo</td>
                        <td class='no-wrap'>$docDate</td>
                        <td class='no-wrap'>$vendorCode</td>
                        <td class='no-wrap'>$consignee</td>
                        <td class='no-wrap'>$itemCode</td>
                        <td class='no-wrap'>$uomCode</td>
                        <td class='numeric-alignment'>$totalQty</td>
                        <td class='numeric-alignment'>$rate</td>
                        <td class='no-wrap'>$deliveryDate</td>
                        </tr>
                    ";
                }
            }

            return [
                'valid_records' => $successRecords,
                'invalid_records'=> $failedRecords,
                'validUI'       => $validUI,
                'invalidUI'     => $invalidUI
            ];
        }
        // v2 (single qty + attributes)
        else if ($version == "v2") {
            foreach ($uploadsData as $uploadData) {
                $totalQty     = $uploadData->qty ?? 0;
                $orderNo      = $uploadData->order_no ?? "";
                $docDate      = Carbon::parse($uploadData->document_date)->format("d-m-Y");
                $vendorCode   = $uploadData->vendor_code ?? "";
                $consignee    = $uploadData->consignee_name ?? "";
                $itemCode     = $uploadData->item_code ?? "";
                $uomCode      = $uploadData->uom_code ?? "";
                $rate         = $uploadData->rate ?? 0;
                $deliveryDate = Carbon::parse($uploadData->delivery_date)->format("d-m-Y");

                $itemAttributes = "";
                foreach ($uploadData->attributes as $itemAttr) {
                    $attributeName  = $itemAttr['attribute_name'];
                    $attributeValue = $itemAttr['attribute_value'];
                    $itemAttributes .= "<span class='badge rounded-pill badge-light-primary'><strong>$attributeName</strong>: $attributeValue</span>";
                }

                if ($uploadData->reason && count($uploadData->reason) > 0) {
                    $failedRecords += 1;
                    $errors = "";
                    foreach ($uploadData->reason as $errIndex => $errorReason) {
                        $errors .= ($errIndex == 0 ? $errorReason : ", " . $errorReason);
                    }
                    $invalidUI .= "
                        <tr>
                        <td class='no-wrap'>$orderNo</td>
                        <td class='no-wrap'>$docDate</td>
                        <td class='no-wrap'>$vendorCode</td>
                        <td class='no-wrap'>$consignee</td>
                        <td class='no-wrap'>$itemCode</td>
                        <td class='no-wrap'>$uomCode</td>
                        <td class='no-wrap'>$itemAttributes</td>
                        <td class='numeric-alignment'>$totalQty</td>
                        <td class='numeric-alignment'>$rate</td>
                        <td class='no-wrap'>$deliveryDate</td>
                        <td class='no-wrap text-danger'>$errors</td>
                        </tr>
                    ";
                } else {
                    $successRecords += 1;
                    $validUI .= "
                        <tr>
                        <td class='no-wrap'>$orderNo</td>
                        <td class='no-wrap'>$docDate</td>
                        <td class='no-wrap'>$vendorCode</td>
                        <td class='no-wrap'>$consignee</td>
                        <td class='no-wrap'>$itemCode</td>
                        <td class='no-wrap'>$uomCode</td>
                        <td class='no-wrap'>$itemAttributes</td>
                        <td class='numeric-alignment'>$totalQty</td>
                        <td class='numeric-alignment'>$rate</td>
                        <td class='no-wrap'>$deliveryDate</td>
                        </tr>
                    ";
                }
            }

            return [
                'valid_records' => $successRecords,
                'invalid_records'=> $failedRecords,
                'validUI'       => $validUI,
                'invalidUI'     => $invalidUI
            ];
        } else {
            return [
                'valid_records'  => $successRecords,
                'invalid_records'=> $failedRecords,
                'validUI'        => $validUI,
                'invalidUI'      => $invalidUI
            ];
        }
    }

    /**
     * Build Valid/Invalid UI rows for item-only review (PO items)
     */
    public static function generateValidInvalidUiItem(Collection $uploadsData) : array
    {
        $successRecords = 0;
        $failedRecords  = 0;
        $validUI = "";
        $invalidUI = "";

        foreach ($uploadsData as $uploadData) {
            $totalQty     = $uploadData->qty ?? 0;
            $itemCode     = $uploadData->item_code ?? "";
            $uomCode      = $uploadData->uom_code ?? "";
            $rate         = $uploadData->rate ?? 0;
            $deliveryDate = Carbon::parse($uploadData->delivery_date)->format("d-m-Y");

            $itemAttributes = "";
            foreach ($uploadData->attributes ?? [] as $itemAttr) {
                $attributeName  = $itemAttr['attribute_name'];
                $attributeValue = $itemAttr['attribute_value'];
                $itemAttributes .= "<span class='badge rounded-pill badge-light-primary'><strong>$attributeName</strong>: $attributeValue</span>";
            }

            if ($uploadData->reason && count($uploadData->reason) > 0) {
                $failedRecords += 1;
                $errors = "";
                foreach ($uploadData->reason as $errIndex => $errorReason) {
                    $errors .= ($errIndex == 0 ? $errorReason : ", " . $errorReason);
                }
                $invalidUI .= "
                    <tr>
                    <td class='no-wrap'>$itemCode</td>
                    <td class='no-wrap'>$uomCode</td>
                    <td class='no-wrap'>$itemAttributes</td>
                    <td class='numeric-alignment'>$totalQty</td>
                    <td class='numeric-alignment'>$rate</td>
                    <td class='no-wrap'>$deliveryDate</td>
                    <td class='no-wrap text-danger'>$errors</td>
                    </tr>
                ";
            } else {
                $successRecords += 1;
                $validUI .= "
                    <tr>
                    <td class='no-wrap'>$itemCode</td>
                    <td class='no-wrap'>$uomCode</td>
                    <td class='no-wrap'>$itemAttributes</td>
                    <td class='numeric-alignment'>$totalQty</td>
                    <td class='numeric-alignment'>$rate</td>
                    <td class='no-wrap'>$deliveryDate</td>
                    </tr>
                ";
            }
        }

        return [
            'valid_records'  => $successRecords,
            'invalid_records'=> $failedRecords,
            'validUI'        => $validUI,
            'invalidUI'      => $invalidUI
        ];
    }
}
