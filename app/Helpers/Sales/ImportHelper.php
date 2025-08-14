<?php

namespace App\Helpers\Sales;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Helpers\SaleModuleHelper;
use App\Helpers\TaxHelper;
use App\Models\Attribute;
use App\Models\BomDetail;
use App\Models\Book;
use App\Models\Customer;
use App\Models\ErpSaleOrder;
use App\Models\ErpSaleOrderTed;
use App\Models\ErpSoDynamicField;
use App\Models\ErpSoItem;
use App\Models\ErpSoItemAttribute;
use App\Models\ErpSoItemBom;
use App\Models\ErpSoItemDelivery;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ImportHelper  
{
    public static function getSoImports(): array
    {
        return [
            'v1' => asset('templates/SalesOrderImportV1.xlsx'),
            'v2' => asset('templates/SalesOrderImportV2.xlsx'),
        ];
    }
    public static function getSoImportHeaders(): array
    {
        return [
            'v1' => "
                <th class = 'no-wrap'>Order No</th>
                <th class = 'no-wrap'>Order Date</th>
                <th class = 'no-wrap'>Customer</th>
                <th class = 'no-wrap'>Consignee</th>
                <th class = 'no-wrap'>Item Code</th>
                <th class = 'no-wrap'>UOM</th>
                <th class = 'numeric-alignment no-wrap'>Total Qty</th>
                <th class = 'numeric-alignment'>Rate</th>
                <th class = 'no-wrap'>Delivery Date</th>
            ",
            'v2' => "
                <th class = 'no-wrap'>Order No</th>
                <th class = 'no-wrap'>Order Date</th>
                <th class = 'no-wrap'>Customer</th>
                <th class = 'no-wrap'>Consignee</th>
                <th class = 'no-wrap'>Item Code</th>
                <th class = 'no-wrap'>UOM</th>
                <th class = 'no-wrap'>Attributes</th>
                <th class = 'numeric-alignment no-wrap'>Qty</th>
                <th class = 'numeric-alignment'>Rate</th>
                <th class = 'no-wrap'>Delivery Date</th>
            ",
        ];
    }
    public static function shufabImportDataSave(Collection $data, int $bookId, int $locationId, $user, string $document_status) : array
    {
        $successfullOrders = 0;
        $failureOrders = 0;
        //Group Company Org
        $organization = Organization::find($user -> organization_id);
        $organizationId = $organization ?-> id ?? null;
        $groupId = $organization ?-> group_id ?? null;
        $companyId = $organization ?-> company_id ?? null;
        //Book
        $book = Book::find($bookId);
        //Location Details
        $location = ErpStore::find($locationId);
        $companyCountryId = null;
        $companyStateId = null;
        $locationAddress = $location ?-> address;
        if ($location && isset($locationAddress)) {
            $companyCountryId = $location->address?->country_id??null;
            $companyStateId = $location->address?->state_id??null;
        } else {
            return [
                'message' => 'Location Address is not specified',
                'status' => 422
            ];
        }
        //Loop through the uploaded data
        $currentOrder = null;
        $addedOrders = [];
        $createdOrderIds = [];
        foreach ($data as $uploadData) {
            $existingError = ($uploadData -> reason);
            if (isset($existingError) && count($existingError) > 0) {
                continue;
            }
            $errors = [];
            $currentOrder = $uploadData -> order_no;
            if (!in_array($currentOrder, $addedOrders)) {
                //New Order - First Create Document Number
                $numberPatternData = Helper::generateDocumentNumberNew($bookId, $uploadData -> document_date);
                if (!isset($numberPatternData)) {
                    return [
                        'message' => "Invalid Book",
                        'status' => 422,
                    ];
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $uploadData -> order_no;
                $regeneratedDocExist = ErpSaleOrder::withDefaultGroupCompanyOrg() -> where('book_id',$bookId)
                    ->where('document_number',$document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    $errors[] = ConstantHelper::DUPLICATE_DOCUMENT_NUMBER;
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    //Skip to the next order
                    continue;
                }
                //Customer Details
                $customer = Customer::find($uploadData -> customer_id);
                if (!isset($customer)) {
                    $errors[] = 'Customer not found';
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                //If Customer is Regular, pick from Customer Master
                $customerPhoneNo = $customer -> mobile ?? null;
                $customerEmail = $customer -> email ?? null;
                $customerGSTIN = $customer -> compliances ?-> gstin_no ?? null;
                //Curreny Id
                $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($customer -> currency_id, $uploadData -> document_date);
                if ($currencyExchangeData['status'] == false) {
                    $errors[] =  $currencyExchangeData['message'];
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                $saleOrder = ErpSaleOrder::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $bookId,
                    'book_code' => $book -> book_code,
                    'document_type' => ConstantHelper::SO_SERVICE_ALIAS,
                    'document_number' => $document_number,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $uploadData -> document_date,
                    'revision_number' => 0,
                    'revision_date' => null,
                    'reference_number' => $uploadData -> order_no,
                    'store_id' => $locationId,
                    'store_code' => $location ?-> store_name,
                    'customer_id' => $customer ?-> id,
                    'customer_email' => $customerEmail,
                    'customer_phone_no' => $customerPhoneNo,
                    'customer_gstin' => $customerGSTIN,
                    'customer_code' => $customer ?-> company_name,
                    'consignee_name' => $uploadData -> consignee_name,
                    'billing_address' => null,
                    'shipping_address' => null,
                    'currency_id' => $customer ?-> currency_id,
                    'currency_code' => $customer -> currency ?-> short_name,
                    'payment_term_id' => $customer -> payment_terms_id,
                    'payment_term_code' => $customer -> paymentTerm ?-> alias,
                    'document_status' => ConstantHelper::DRAFT,
                    'approval_level' => 1,
                    'remarks' => '',
                    'org_currency_id' => $currencyExchangeData['data']['org_currency_id'],
                    'org_currency_code' => $currencyExchangeData['data']['org_currency_code'],
                    'org_currency_exg_rate' => $currencyExchangeData['data']['org_currency_exg_rate'],
                    'comp_currency_id' => $currencyExchangeData['data']['comp_currency_id'],
                    'comp_currency_code' => $currencyExchangeData['data']['comp_currency_code'],
                    'comp_currency_exg_rate' => $currencyExchangeData['data']['comp_currency_exg_rate'],
                    'group_currency_id' => $currencyExchangeData['data']['group_currency_id'],
                    'group_currency_code' => $currencyExchangeData['data']['group_currency_code'],
                    'group_currency_exg_rate' => $currencyExchangeData['data']['group_currency_exg_rate'],
                    'total_item_value' => 0,
                    'total_discount_value' => 0,
                    'total_tax_value' => 0,
                    'total_expense_value' => 0,
                ]);
                //Addresses
                $customerAddresses = $customer -> addresses;
                $customerBillingAddress = $customerAddresses -> whereIn('type', ['billing', 'both']) -> first();
                if (isset($customerBillingAddress)) {
                    $billingAddress = $saleOrder -> billing_address_details() -> create([
                        'address' => $customerBillingAddress -> address,
                        'country_id' => $customerBillingAddress -> country_id,
                        'state_id' => $customerBillingAddress -> state_id,
                        'city_id' => $customerBillingAddress -> city_id,
                        'type' => 'billing',
                        'pincode' => $customerBillingAddress -> pincode,
                        'phone' => $customerBillingAddress -> phone,
                        'fax_number' => $customerBillingAddress -> fax_number
                    ]);
                } else {
                    $errors[] = "Customer Billing Address not setup";
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                // Shipping Address
                $customerShippingAddress = $customerAddresses -> whereIn('type', ['shipping', 'both']) -> first();
                if (isset($customerShippingAddress)) {
                    $shippingAddress = $saleOrder -> shipping_address_details() -> create([
                        'address' => $customerShippingAddress -> address,
                        'country_id' => $customerShippingAddress -> country_id,
                        'state_id' => $customerShippingAddress -> state_id,
                        'city_id' => $customerShippingAddress -> city_id,
                        'type' => 'shipping',
                        'pincode' => $customerShippingAddress -> pincode,
                        'phone' => $customerShippingAddress -> phone,
                        'fax_number' => $customerShippingAddress -> fax_number
                    ]);
                } else {
                    $errors[] = "Customer Billing Address not setup";
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                //Location Address
                $orgLocationAddress = $locationAddress;
                $locationAddress = $saleOrder -> location_address_details() -> create([
                    'address' => $orgLocationAddress -> address,
                    'country_id' => $orgLocationAddress -> country_id,
                    'state_id' => $orgLocationAddress -> state_id,
                    'city_id' => $orgLocationAddress -> city_id,
                    'type' => 'location',
                    'pincode' => $orgLocationAddress -> pincode,
                    'phone' => $orgLocationAddress -> phone,
                    'fax_number' => $orgLocationAddress -> fax_number
                ]);
                //Update addresses to Sales Order
                $saleOrder -> billing_address = isset($billingAddress) ? $billingAddress -> id : null;
                $saleOrder -> shipping_address = isset($shippingAddress) ? $shippingAddress -> id : null;
                $saleOrder -> save();
                //Add the Sales Order to tracking Array
                array_push($addedOrders, $uploadData -> order_no);
                $createdOrderIds[$uploadData -> order_no] = $saleOrder;

                //Now check for any dynamic fields
                $bookDynamicFields = $uploadData -> dynamic_fields;
                foreach ($bookDynamicFields as $dynamicField) {
                        ErpSoDynamicField::create([
                            'header_id' => $saleOrder -> id,
                            'dynamic_field_id' => $dynamicField -> dyn_header_id,
                            'dynamic_field_detail_id' => $dynamicField -> dyn_detail_id,
                            'name' => $dynamicField -> name,
                            'value' => $dynamicField -> value,
                        ]);
                }
            }
            //Check if the current order has been created
            if (!isset($createdOrderIds[$uploadData -> order_no] -> id)) {
                continue;
            }
            
            //Now move to item (For Shufab loop through 14 sizes)
            $item = Item::find($uploadData -> item_id);
            if (!isset($item)) {
                $errors[] = 'Item not found';
                $uploadData -> reason = json_encode($errors);
                $uploadData -> save();
                continue;
            }
            for ($i=1; $i <= 14; $i++) {
                //Build the attributes
                $keyName = 'size_' . $i;
                $attribute = Attribute::whereHas('attributeGroup', function ($groupQuery) {
                    $groupQuery -> withDefaultGroupCompanyOrg() -> whereRaw('LOWER(name) = ?', ['size']);
                }) -> where('value', $i) -> first();
                if (!$attribute) {
                    $errors[] = "Item Attribute Size - $i not found";
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                $attributesArray = [
                    'attribute_id' => $attribute -> id,
                    'attribute_value' => $i
                ];
                //Check BOM
                $bomDetails = ItemHelper::checkItemBomExists($uploadData -> item_id, $attributesArray);
                if (!isset($bomDetails['bom_id'])) {
                    $errors[] = "Bom not found";
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                //Verify UOM details
                if (isset($uploadData -> uom_code) && !isset($uploadData -> uom_id)) {
                    $errors[] = "UOM Not found";
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                //Assign Item UOM if not specified by user
                if (!isset($uploadData -> uom_id) && !isset($uploadData -> uom_code)) {
                    $uploadData -> uom_id = $item -> uom_id;
                    $uploadData -> uom_code = $item -> uom ?-> name;
                }
                if (!($uploadData -> rate)) {
                    $errors[] = "Rate not specified";
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                //Rate and Qty are set then proceed
                if (isset($uploadData -> {$keyName}) && $uploadData -> {$keyName} > 0 && isset($uploadData -> rate)) {
                    //Item is there
                    $hsnId = $item -> hsn_id;
                    $itemValue = $uploadData -> {$keyName} * $uploadData -> rate;
                    $itemTax = 0;
                    $itemPrice = $itemValue / $uploadData -> {$keyName};
                    $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                    $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;
                    //Calculate Taxes
                    $taxDetails = SaleModuleHelper::checkTaxApplicability($customer ?-> id, $bookId) ? 
                    TaxHelper::calculateTax($hsnId, $itemPrice, $companyCountryId, $companyStateId, $partyCountryId , $partyStateId, 'sale') : [];
                    if (isset($taxDetails) && count($taxDetails) > 0) {
                        foreach ($taxDetails as $taxDetail) {
                            $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $itemValue);
                        }
                    }
                    //Delivery Date
                    if (!isset($uploadData -> delivery_date)) {
                        $uploadData -> delivery_date = Carbon::now() -> format('Y-m-d');
                    }
                    $inventoryUomQty = ItemHelper::convertToBaseUom($item -> id, $uploadData -> uom_id, $uploadData -> {$keyName});
                    //Save the Item
                    $soItem = ErpSoItem::create([
                        'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                        'bom_id' => $bomDetails['bom_id'],
                        'item_id' => $item -> id,
                        'item_code' => $item -> item_code,
                        'item_name' => $item -> item_name,
                        'hsn_id' => $item -> hsn_id,
                        'hsn_code' => $item -> hsn ?-> code,
                        'uom_id' => $uploadData -> uom_id, //Need to change
                        'uom_code' => $uploadData -> uom_code,
                        'order_qty' => $uploadData -> {$keyName},
                        'invoice_qty' => 0,
                        'inventory_uom_id' => $item -> uom_id,
                        'inventory_uom_code' => $item -> uom_name,
                        'inventory_uom_qty' => $inventoryUomQty,
                        'rate' => $uploadData -> rate,
                        'delivery_date' => $uploadData -> delivery_date,
                        'item_discount_amount' => 0,
                        'header_discount_amount' => 0,
                        'item_expense_amount' => 0, //Need to change
                        'header_expense_amount' => 0, //Need to change
                        'tax_amount' => $itemTax,
                        'total_item_amount' => ($uploadData -> {$keyName} * $uploadData -> rate) + $itemTax,
                        'company_currency_id' => null,
                        'company_currency_exchange_rate' => null,
                        'group_currency_id' => null,
                        'group_currency_exchange_rate' => null,
                        'remarks' => null,
                    ]);
                    if (isset($taxDetails) && count($taxDetails) > 0) {
                        foreach ($taxDetails as $taxDetail) {
                            $soItemTedForDiscount = ErpSaleOrderTed::create(
                                [
                                    'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                                    'so_item_id' => $soItem -> id,
                                    'ted_type' => 'Tax',
                                    'ted_level' => 'D',
                                    'ted_id' => $taxDetail['id'],
                                    'ted_group_code' => $taxDetail['tax_group'],
                                    'ted_name' => $taxDetail['tax_type'],
                                    'assessment_amount' => $itemValue,
                                    'ted_percentage' => (double)$taxDetail['tax_percentage'],
                                    'ted_amount' => ((double)$taxDetail['tax_percentage'] / 100 * $itemValue),
                                    'applicable_type' => 'Collection',
                                ]
                            );
                        }
                    }
                    //Customizable BOM
                    if ($bomDetails['customizable'] == "yes") {
                        $bomDetailRecords = BomDetail::with('attributes') -> where('bom_id', $bomDetails['bom_id']) -> get();
                        foreach ($bomDetailRecords as $currentBomDetail) {
                            $itemAttributes = [];
                            foreach ($currentBomDetail -> attributes as $bomAttr) {
                                array_push($itemAttributes, [
                                    'attribute_group_id' => $bomAttr -> attribute_name,
                                    'attribute_name' => $bomAttr -> headerAttribute ?-> name,
                                    'attribute_value' => $bomAttr -> headerAttributeValue ?-> value,
                                    'attribute_value_id' => $bomAttr -> attribute_value,
                                    'attribute_id' => $bomAttr -> id,
                                ]);
                            }
                            ErpSoItemBom::create([
                                'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                                'so_item_id' => $soItem -> id,
                                'bom_id' => $bomDetails['bom_id'],
                                'bom_detail_id' => $currentBomDetail -> id,
                                'uom_id' => $currentBomDetail -> uom_id,
                                'item_id' => $currentBomDetail -> item_id,
                                'item_code' => $currentBomDetail -> item_code,
                                'item_attributes' => ($itemAttributes),
                                'qty' => $currentBomDetail -> qty,
                                'station_id' => $currentBomDetail -> station_id,
                                'station_name' => $currentBomDetail -> station_name
                            ]);
                        }
                    }
                    //Item Attributes
                    $itemAttributes = $item -> itemAttributes;
                    foreach ($itemAttributes as $itemAttr) {
                        $itemAttribute = ErpSoItemAttribute::create(
                            [
                                'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                                'so_item_id' => $soItem -> id,
                                'item_attribute_id' => $itemAttr -> id,
                                'item_code' => $soItem -> item_code,
                                'attribute_name' => $attribute -> group ?-> name,
                                'attr_name' => $attribute -> group ?-> id,
                                'attribute_value' => $attribute -> value,
                                'attr_value' => $attribute -> id,
                            ]
                        );
                    }
                    //Item Deliveries
                    ErpSoItemDelivery::create([
                        'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                        'so_item_id' => $soItem -> id,
                        'ledger_id' => null,
                        'qty' => $uploadData -> {$keyName},
                        'invoice_qty' => 0,
                        'delivery_date' => $uploadData -> delivery_date,
                    ]);      
                }
            }
        }
        //Final Status Update of Orders and it's Item
        foreach ($createdOrderIds as $index => $createdOrder) {
            $successfullOrders += 1;
            $items = ErpSoItem::where('sale_order_id', $createdOrder -> id) -> get();
            $totalItemTax = 0;
            $totalItemValue = 0;
            foreach ($items as $item) {
                $totalItemTax += $item -> tax_amount;
                $totalItemValue += $item -> total_item_amount;
            }
            $saleOrder = ErpSaleOrder::where('id', $createdOrder -> id) -> first();
            if (isset($saleOrder)) {
                $saleOrder -> total_tax_value = $totalItemTax;
                $saleOrder -> total_amount = $totalItemValue;
                $saleOrder -> total_item_value = $totalItemValue;
                if ($document_status == ConstantHelper::SUBMITTED) {
                    $bookId = $saleOrder->book_id;
                    $docId = $saleOrder->id;
                    $remarks = $saleOrder->remarks;
                    $attachments = [];
                    $currentLevel = $saleOrder->approval_level;
                    $revisionNumber = $saleOrder->revision_number ?? 0;
                    $actionType = 'submit'; // Approve // reject // submit
                    $modelName = get_class($saleOrder);
                    $totalValue = $saleOrder->total_amount ?? 0;
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                    $saleOrder->document_status = $approveDocument['approvalStatus'] ?? $saleOrder->document_status;
                }
                $saleOrder -> save();
                //Update the migrated status
                $uploadData -> is_migrated = "1";
                $uploadData -> save();
            }
        }
        if ($successfullOrders) {
            return [
                'message' => "$successfullOrders Sales Order imported Successfully",
                'status' => 200 
            ];
        } else {
            return [
                'message' => "Order Import failed due to multiple errors. Please check the uploaded file again.",
                'status' => 422 
            ];
        }
    }

    public static function v2ImportDataSave(Collection $data, int $bookId, int $locationId, $user, string $document_status) : array
    {
        $successfullOrders = 0;
        $failureOrders = 0;
        //Group Company Org
        $organization = Organization::find($user -> organization_id);
        $organizationId = $organization ?-> id ?? null;
        $groupId = $organization ?-> group_id ?? null;
        $companyId = $organization ?-> company_id ?? null;
        //Book
        $book = Book::find($bookId);
        //Location Details
        $location = ErpStore::find($locationId);
        $companyCountryId = null;
        $companyStateId = null;
        $locationAddress = $location ?-> address;
        if ($location && isset($locationAddress)) {
            $companyCountryId = $location->address?->country_id??null;
            $companyStateId = $location->address?->state_id??null;
        } else {
            return [
                'message' => 'Location Address is not specified',
                'status' => 422
            ];
        }
        //Loop through the uploaded data
        $currentOrder = null;
        $addedOrders = [];
        $createdOrderIds = [];
        foreach ($data as $uploadData) {
            //Skip if error is found
            $existingError = ($uploadData -> reason);
            if (isset($existingError) && count($existingError) > 0) {
                continue;
            }
            $errors = [];
            $currentOrder = $uploadData -> order_no;
            if (!in_array($currentOrder, $addedOrders)) {
                //New Order - First Create Document Number
                $numberPatternData = Helper::generateDocumentNumberNew($bookId, $uploadData -> document_date);
                if (!isset($numberPatternData)) {
                    return [
                        'message' => "Invalid Book",
                        'status' => 422,
                    ];
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $uploadData -> order_no;
                $regeneratedDocExist = ErpSaleOrder::withDefaultGroupCompanyOrg() -> where('book_id',$bookId)
                ->where('document_number',$document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    $errors[] = ConstantHelper::DUPLICATE_DOCUMENT_NUMBER;
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    //Skip to the next order
                    continue;
                }
                //Customer Details
                $customer = Customer::find($uploadData -> customer_id);
                if (!isset($customer)) {
                    $errors[] = 'Customer not found';
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                //If Customer is Regular, pick from Customer Master
                $customerPhoneNo = $customer -> mobile ?? null;
                $customerEmail = $customer -> email ?? null;
                $customerGSTIN = $customer -> compliances ?-> gstin_no ?? null;
                //Curreny Id
                $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($customer -> currency_id, $uploadData -> document_date);
                if ($currencyExchangeData['status'] == false) {
                    $errors[] =  $currencyExchangeData['message'];
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                $saleOrder = ErpSaleOrder::create([
                    'organization_id' => $organizationId,
                    'group_id' => $groupId,
                    'company_id' => $companyId,
                    'book_id' => $bookId,
                    'book_code' => $book -> book_code,
                    'document_type' => ConstantHelper::SO_SERVICE_ALIAS,
                    'document_number' => $document_number,
                    'doc_number_type' => $numberPatternData['type'],
                    'doc_reset_pattern' => $numberPatternData['reset_pattern'],
                    'doc_prefix' => $numberPatternData['prefix'],
                    'doc_suffix' => $numberPatternData['suffix'],
                    'doc_no' => $numberPatternData['doc_no'],
                    'document_date' => $uploadData -> document_date,
                    'revision_number' => 0,
                    'revision_date' => null,
                    'reference_number' => $uploadData -> order_no,
                    'store_id' => $locationId,
                    'store_code' => $location ?-> store_name,
                    'customer_id' => $customer ?-> id,
                    'customer_email' => $customerEmail,
                    'customer_phone_no' => $customerPhoneNo,
                    'customer_gstin' => $customerGSTIN,
                    'customer_code' => $customer ?-> company_name,
                    'consignee_name' => $uploadData -> consignee_name,
                    'billing_address' => null,
                    'shipping_address' => null,
                    'currency_id' => $customer ?-> currency_id,
                    'currency_code' => $customer -> currency ?-> short_name,
                    'payment_term_id' => $customer -> payment_terms_id,
                    'payment_term_code' => $customer -> paymentTerm ?-> alias,
                    'document_status' => ConstantHelper::DRAFT,
                    'approval_level' => 1,
                    'remarks' => '',
                    'org_currency_id' => $currencyExchangeData['data']['org_currency_id'],
                    'org_currency_code' => $currencyExchangeData['data']['org_currency_code'],
                    'org_currency_exg_rate' => $currencyExchangeData['data']['org_currency_exg_rate'],
                    'comp_currency_id' => $currencyExchangeData['data']['comp_currency_id'],
                    'comp_currency_code' => $currencyExchangeData['data']['comp_currency_code'],
                    'comp_currency_exg_rate' => $currencyExchangeData['data']['comp_currency_exg_rate'],
                    'group_currency_id' => $currencyExchangeData['data']['group_currency_id'],
                    'group_currency_code' => $currencyExchangeData['data']['group_currency_code'],
                    'group_currency_exg_rate' => $currencyExchangeData['data']['group_currency_exg_rate'],
                    'total_item_value' => 0,
                    'total_discount_value' => 0,
                    'total_tax_value' => 0,
                    'total_expense_value' => 0,
                ]);
                //Addresses
                $customerAddresses = $customer -> addresses;
                $customerBillingAddress = $customerAddresses -> whereIn('type', ['billing', 'both']) -> first();
                if (isset($customerBillingAddress)) {
                    $billingAddress = $saleOrder -> billing_address_details() -> create([
                        'address' => $customerBillingAddress -> address,
                        'country_id' => $customerBillingAddress -> country_id,
                        'state_id' => $customerBillingAddress -> state_id,
                        'city_id' => $customerBillingAddress -> city_id,
                        'type' => 'billing',
                        'pincode' => $customerBillingAddress -> pincode,
                        'phone' => $customerBillingAddress -> phone,
                        'fax_number' => $customerBillingAddress -> fax_number
                    ]);
                } else {
                    $errors[] = "Customer Billing Address not setup";
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                // Shipping Address
                $customerShippingAddress = $customerAddresses -> whereIn('type', ['shipping', 'both']) -> first();
                if (isset($customerShippingAddress)) {
                    $shippingAddress = $saleOrder -> shipping_address_details() -> create([
                        'address' => $customerShippingAddress -> address,
                        'country_id' => $customerShippingAddress -> country_id,
                        'state_id' => $customerShippingAddress -> state_id,
                        'city_id' => $customerShippingAddress -> city_id,
                        'type' => 'shipping',
                        'pincode' => $customerShippingAddress -> pincode,
                        'phone' => $customerShippingAddress -> phone,
                        'fax_number' => $customerShippingAddress -> fax_number
                    ]);
                } else {
                    $errors[] = "Customer Billing Address not setup";
                    $uploadData -> reason = json_encode($errors);
                    $uploadData -> save();
                    continue;
                }
                //Location Address
                $orgLocationAddress = $locationAddress;
                $locationAddress = $saleOrder -> location_address_details() -> create([
                    'address' => $orgLocationAddress -> address,
                    'country_id' => $orgLocationAddress -> country_id,
                    'state_id' => $orgLocationAddress -> state_id,
                    'city_id' => $orgLocationAddress -> city_id,
                    'type' => 'location',
                    'pincode' => $orgLocationAddress -> pincode,
                    'phone' => $orgLocationAddress -> phone,
                    'fax_number' => $orgLocationAddress -> fax_number
                ]);
                //Update addresses to Sales Order
                $saleOrder -> billing_address = isset($billingAddress) ? $billingAddress -> id : null;
                $saleOrder -> shipping_address = isset($shippingAddress) ? $shippingAddress -> id : null;
                $saleOrder -> save();
                //Add the Sales Order to tracking Array
                array_push($addedOrders, $uploadData -> order_no);
                $createdOrderIds[$uploadData -> order_no] = $saleOrder;
            }
            //Check if the current order has been created
            if (!isset($createdOrderIds[$uploadData -> order_no] -> id)) {
                continue;
            }
            //Now check for any dynamic fields
            $bookDynamicFields = $book -> dynamic_fields;
            foreach ($bookDynamicFields as $bookDynamicField) {
                $dynamicField = $bookDynamicField -> dynamic_field;
                foreach ($dynamicField -> details as $dynamicFieldDetail) {
                    ErpSoDynamicField::create([
                        'header_id' => $saleOrder -> id,
                        'dynamic_field_id' => $dynamicField -> id,
                        'dynamic_field_detail_id' => $dynamicFieldDetail -> id,
                        'name' => $dynamicFieldDetail -> name,
                        'value' => null
                    ]);
                }
            }
            //Now move to item
            $item = Item::find($uploadData -> item_id);
            if (!isset($item)) {
                $errors[] = 'Item not found';
                $uploadData -> reason = json_encode($errors);
                $uploadData -> save();
                continue;
            }
            //Build the attributes
            $attributesArray = $uploadData -> attributes ?? [];
            //Check BOM
            $bomDetails = ItemHelper::checkItemBomExists($uploadData -> item_id, $attributesArray);
            if (!isset($bomDetails['bom_id'])) {
                $errors[] = "Bom not found";
                $uploadData -> reason = json_encode($errors);
                $uploadData -> save();
                continue;
            }
            //Verify UOM details
            if (isset($uploadData -> uom_code) && !isset($uploadData -> uom_id)) {
                $errors[] = "UOM Not found";
                $uploadData -> reason = json_encode($errors);
                $uploadData -> save();
                continue;
            }
            //Assign Item UOM if not specified by user
            if (!isset($uploadData -> uom_id) && !isset($uploadData -> uom_code)) {
                $uploadData -> uom_id = $item -> uom_id;
                $uploadData -> uom_code = $item -> uom ?-> name;
            }
            if (!($uploadData -> rate)) {
                $errors[] = "Rate not specified";
                $uploadData -> reason = json_encode($errors);
                $uploadData -> save();
                continue;
            }
            //Rate and Qty are set then proceed
            if (isset($uploadData -> qty) && $uploadData -> qty > 0 && isset($uploadData -> rate)) {
                //Item is there
                $hsnId = $item -> hsn_id;
                $itemValue = $uploadData -> qty * $uploadData -> rate;
                $itemTax = 0;
                $itemPrice = $itemValue / $uploadData -> qty;
                $partyCountryId = isset($shippingAddress) ? $shippingAddress -> country_id : null;
                $partyStateId = isset($shippingAddress) ? $shippingAddress -> state_id : null;
                //Calculate Taxes
                $taxDetails = SaleModuleHelper::checkTaxApplicability($customer ?-> id, $bookId) ? 
                TaxHelper::calculateTax($hsnId, $itemPrice, $companyCountryId, $companyStateId, $partyCountryId , $partyStateId, 'sale') : [];
                if (isset($taxDetails) && count($taxDetails) > 0) {
                    foreach ($taxDetails as $taxDetail) {
                        $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $itemValue);
                    }
                }
                //Delivery Date
                if (!isset($uploadData -> delivery_date)) {
                    $uploadData -> delivery_date = Carbon::now() -> format('Y-m-d');
                }
                $inventoryUomQty = ItemHelper::convertToBaseUom($item -> id, $uploadData -> uom_id, $uploadData -> qty);
                //Save the Item
                $soItem = ErpSoItem::create([
                    'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                    'bom_id' => $bomDetails['bom_id'],
                    'item_id' => $item -> id,
                    'item_code' => $item -> item_code,
                    'item_name' => $item -> item_name,
                    'hsn_id' => $item -> hsn_id,
                    'hsn_code' => $item -> hsn ?-> code,
                    'uom_id' => $uploadData -> uom_id, //Need to change
                    'uom_code' => $uploadData -> uom_code,
                    'order_qty' => $uploadData -> qty,
                    'invoice_qty' => 0,
                    'inventory_uom_id' => $item -> uom_id,
                    'inventory_uom_code' => $item -> uom_name,
                    'inventory_uom_qty' => $inventoryUomQty,
                    'rate' => $uploadData -> rate,
                    'delivery_date' => $uploadData -> delivery_date,
                    'item_discount_amount' => 0,
                    'header_discount_amount' => 0,
                    'item_expense_amount' => 0, //Need to change
                    'header_expense_amount' => 0, //Need to change
                    'tax_amount' => $itemTax,
                    'total_item_amount' => ($uploadData -> qty * $uploadData -> rate) + $itemTax,
                    'company_currency_id' => null,
                    'company_currency_exchange_rate' => null,
                    'group_currency_id' => null,
                    'group_currency_exchange_rate' => null,
                    'remarks' => null,
                ]);
                if (isset($taxDetails) && count($taxDetails) > 0) {
                    foreach ($taxDetails as $taxDetail) {
                        $soItemTedForDiscount = ErpSaleOrderTed::create(
                            [
                                'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                                'so_item_id' => $soItem -> id,
                                'ted_type' => 'Tax',
                                'ted_level' => 'D',
                                'ted_id' => $taxDetail['id'],
                                'ted_group_code' => $taxDetail['tax_group'],
                                'ted_name' => $taxDetail['tax_type'],
                                'assessment_amount' => $itemValue,
                                'ted_percentage' => (double)$taxDetail['tax_percentage'],
                                'ted_amount' => ((double)$taxDetail['tax_percentage'] / 100 * $itemValue),
                                'applicable_type' => 'Collection',
                            ]
                        );
                    }
                }
                //Customizable BOM
                if ($bomDetails['customizable'] == "yes") {
                    $bomDetailRecords = BomDetail::with('attributes') -> where('bom_id', $bomDetails['bom_id']) -> get();
                    foreach ($bomDetailRecords as $currentBomDetail) {
                        $itemAttributes = [];
                        foreach ($currentBomDetail -> attributes as $bomAttr) {
                            array_push($itemAttributes, [
                                'attribute_group_id' => $bomAttr -> attribute_name,
                                'attribute_name' => $bomAttr -> headerAttribute ?-> name,
                                'attribute_value' => $bomAttr -> headerAttributeValue ?-> value,
                                'attribute_value_id' => $bomAttr -> attribute_value,
                                'attribute_id' => $bomAttr -> id,
                            ]);
                        }
                        ErpSoItemBom::create([
                            'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                            'so_item_id' => $soItem -> id,
                            'bom_id' => $bomDetails['bom_id'],
                            'bom_detail_id' => $currentBomDetail -> id,
                            'uom_id' => $currentBomDetail -> uom_id,
                            'item_id' => $currentBomDetail -> item_id,
                            'item_code' => $currentBomDetail -> item_code,
                            'item_attributes' => ($itemAttributes),
                            'qty' => $currentBomDetail -> qty,
                            'station_id' => $currentBomDetail -> station_id,
                            'station_name' => $currentBomDetail -> station_name
                        ]);
                    }
                }
                //Item Attributes
                foreach ($attributesArray as $itemAttr) {
                    ErpSoItemAttribute::create(
                        [
                            'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                            'so_item_id' => $soItem -> id,
                            'item_attribute_id' => $itemAttr['item_attribute_id'],
                            'item_code' => $soItem -> item_code,
                            'attribute_name' => $itemAttr['attribute_name'],
                            'attr_name' => $itemAttr['attr_name'],
                            'attribute_value' => $itemAttr['attribute_value'],
                            'attr_value' => $itemAttr['attr_value'],
                        ]
                    );
                }
                //Item Deliveries
                ErpSoItemDelivery::create([
                    'sale_order_id' => $createdOrderIds[$uploadData -> order_no] -> id,
                    'so_item_id' => $soItem -> id,
                    'ledger_id' => null,
                    'qty' => $uploadData -> qty,
                    'invoice_qty' => 0,
                    'delivery_date' => $uploadData -> delivery_date,
                ]);      
            }
        
        }
        //Final Status Update of Orders and it's Item
        foreach ($createdOrderIds as $index => $createdOrder) {
            $successfullOrders += 1;
            $items = ErpSoItem::where('sale_order_id', $createdOrder -> id) -> get();
            $totalItemTax = 0;
            $totalItemValue = 0;
            foreach ($items as $item) {
                $totalItemTax += $item -> tax_amount;
                $totalItemValue += $item -> total_item_amount;
            }
            $saleOrder = ErpSaleOrder::where('id', $createdOrder -> id) -> first();
            if (isset($saleOrder)) {
                $saleOrder -> total_tax_value = $totalItemTax;
                $saleOrder -> total_amount = $totalItemValue;
                $saleOrder -> total_item_value = $totalItemValue;
                if ($document_status == ConstantHelper::SUBMITTED) {
                    $bookId = $saleOrder->book_id;
                    $docId = $saleOrder->id;
                    $remarks = $saleOrder->remarks;
                    $attachments = [];
                    $currentLevel = $saleOrder->approval_level;
                    $revisionNumber = $saleOrder->revision_number ?? 0;
                    $actionType = 'submit'; // Approve // reject // submit
                    $modelName = get_class($saleOrder);
                    $totalValue = $saleOrder->total_amount ?? 0;
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                    $saleOrder->document_status = $approveDocument['approvalStatus'] ?? $saleOrder->document_status;
                }
                $saleOrder -> save();
                //Update the migrated status
                $uploadData -> is_migrated = "1";
                $uploadData -> save();
            }
        }
        if ($successfullOrders) {
            return [
                'message' => "$successfullOrders Sales Order imported Successfully",
                'status' => 200 
            ];
        } else {
            return [
                'message' => "Order Import failed due to multiple errors. Please check the uploaded file again.",
                'status' => 422 
            ];
        }
    }

    public static function generateValidInvalidUi(string $version, Collection $uploadsData) : array
    {
        $successRecords = 0;
        $failedRecords = 0;
        $validUI = "";
        $invalidUI = "";
        //Shufab
        if ($version == "v1") {
            foreach ($uploadsData as $uploadData) {
                $totalQty = 0;
                for ($i=1; $i <= 14; $i++) { 
                    $totalQty += $uploadData -> {'size_' . $i};
                }
                $orderNo = $uploadData -> order_no ?? "";
                $docDate = Carbon::parse($uploadData -> document_date) -> format("d-m-Y");
                $customerCode = $uploadData -> customer_code ?? "";
                $consigneeName = $uploadData -> consignee_name ?? "";
                $itemCode = $uploadData -> item_code ?? "";
                $uomCode = $uploadData -> uom_code ?? "";
                $rate = $uploadData -> rate ?? 0;
                $deliveryDate = Carbon::parse($uploadData -> delivery_date) -> format("d-m-Y");
                //Invalid Rows
                if ($uploadData -> reason && count($uploadData -> reason) > 0) {
                    $failedRecords += 1;
                    $errors = "";
                    foreach ($uploadData -> reason as $errIndex => $errorReason) {
                        $errors .= ($errIndex == 0 ? $errorReason : ", " . $errorReason);
                    }
                    $invalidUI .= "
                    <tr>
                    <td class = 'no-wrap'>$orderNo</td>
                    <td class = 'no-wrap'>$docDate</td>
                    <td class = 'no-wrap'>$customerCode</td>
                    <td class = 'no-wrap'>$consigneeName</td>
                    <td class = 'no-wrap'>$itemCode</td>
                    <td class = 'no-wrap'>$uomCode</td>
                    <td class = 'numeric-alignment'>$totalQty</td>
                    <td class = 'numeric-alignment'>$rate</td>
                    <td class = 'no-wrap'>$deliveryDate</td>
                    <td class = 'no-wrap text-danger'>$errors</td>
                    </tr>
                    ";
                } else {
                    $successRecords += 1;
                    $validUI .= "
                    <tr>
                    <td class = 'no-wrap'>$orderNo</td>
                    <td class = 'no-wrap'>$docDate</td>
                    <td class = 'no-wrap'>$customerCode</td>
                    <td class = 'no-wrap'>$consigneeName</td>
                    <td class = 'no-wrap'>$itemCode</td>
                    <td class = 'no-wrap'>$uomCode</td>
                    <td class = 'numeric-alignment'>$totalQty</td>
                    <td class = 'numeric-alignment'>$rate</td>
                    <td class = 'no-wrap'>$deliveryDate</td>
                    </tr>
                    ";
                }
            }
            return [
                'valid_records' => $successRecords,
                'invalid_records' => $failedRecords,
                'validUI' => $validUI,
                'invalidUI' => $invalidUI 
            ];
        } 
        //Common
        else if ($version == "v2") {
            foreach ($uploadsData as $uploadData) {
                $totalQty = $uploadData -> qty ?? 0;
                $orderNo = $uploadData -> order_no ?? "";
                $docDate = Carbon::parse($uploadData -> document_date) -> format("d-m-Y");
                $customerCode = $uploadData -> customer_code ?? "";
                $consigneeName = $uploadData -> consignee_name ?? "";
                $itemCode = $uploadData -> item_code ?? "";
                $uomCode = $uploadData -> uom_code ?? "";
                $rate = $uploadData -> rate ?? 0;
                $deliveryDate = Carbon::parse($uploadData -> delivery_date) -> format("d-m-Y");
                $itemAttributes = "";
                foreach ($uploadData -> attributes as $itemAttr) {
                    $attributeName = $itemAttr['attribute_name'];
                    $attributeValue = $itemAttr['attribute_value'];
                    $itemAttributes .= "<span class='badge rounded-pill badge-light-primary'><strong>$attributeName</strong>: $attributeValue</span>";
                }
                //Invalid Rows
                if ($uploadData -> reason && count($uploadData -> reason) > 0) {
                    $failedRecords += 1;
                    $errors = "";
                    foreach ($uploadData -> reason as $errIndex => $errorReason) {
                        $errors .= ($errIndex == 0 ? $errorReason : ", " . $errorReason);
                    }
                    $invalidUI .= "
                    <tr>
                    <td class = 'no-wrap'>$orderNo</td>
                    <td class = 'no-wrap'>$docDate</td>
                    <td class = 'no-wrap'>$customerCode</td>
                    <td class = 'no-wrap'>$consigneeName</td>
                    <td class = 'no-wrap'>$itemCode</td>
                    <td class = 'no-wrap'>$uomCode</td>
                    <td class = 'no-wrap'>$itemAttributes</td>
                    <td class = 'numeric-alignment'>$totalQty</td>
                    <td class = 'numeric-alignment'>$rate</td>
                    <td class = 'no-wrap'>$deliveryDate</td>
                    <td class = 'no-wrap text-danger'>$errors</td>
                    </tr>
                    ";
                } else {
                    $successRecords += 1;
                    $validUI .= "
                    <tr>
                    <td class = 'no-wrap'>$orderNo</td>
                    <td class = 'no-wrap'>$docDate</td>
                    <td class = 'no-wrap'>$customerCode</td>
                    <td class = 'no-wrap'>$consigneeName</td>
                    <td class = 'no-wrap'>$itemCode</td>
                    <td class = 'no-wrap'>$uomCode</td>
                    <td class = 'no-wrap'>$itemAttributes</td>
                    <td class = 'numeric-alignment'>$totalQty</td>
                    <td class = 'numeric-alignment'>$rate</td>
                    <td class = 'no-wrap'>$deliveryDate</td>
                    </tr>
                    ";
                }
            }
            return [
                'valid_records' => $successRecords,
                'invalid_records' => $failedRecords,
                'validUI' => $validUI,
                'invalidUI' => $invalidUI 
            ];
        } else {
            return [
                'valid_records' => $successRecords,
                'invalid_records' => $failedRecords,
                'validUI' => $validUI,
                'invalidUI' => $invalidUI 
            ];
        }
    }
    public static function generateValidInvalidUiItem(Collection $uploadsData) : array
    {
        $successRecords = 0;
        $failedRecords = 0;
        $validUI = "";
        $invalidUI = "";

        foreach ($uploadsData as $uploadData) {
            $totalQty = $uploadData -> qty ?? 0;
            $itemCode = $uploadData -> item_code ?? "";
            $uomCode = $uploadData -> uom_code ?? "";
            $rate = $uploadData -> rate ?? 0;
            $deliveryDate = Carbon::parse($uploadData -> delivery_date) -> format("d-m-Y");
            $itemAttributes = "";
            foreach ($uploadData -> attributes ?? [] as $itemAttr) {
                $attributeName = $itemAttr['attribute_name'];
                $attributeValue = $itemAttr['attribute_value'];
                $itemAttributes .= "<span class='badge rounded-pill badge-light-primary'><strong>$attributeName</strong>: $attributeValue</span>";
            }
            //Invalid Rows
            if ($uploadData -> reason && count($uploadData -> reason) > 0) {
                $failedRecords += 1;
                $errors = "";
                foreach ($uploadData -> reason as $errIndex => $errorReason) {
                    $errors .= ($errIndex == 0 ? $errorReason : ", " . $errorReason);
                }
                $invalidUI .= "
                <tr>
                <td class = 'no-wrap'>$itemCode</td>
                <td class = 'no-wrap'>$uomCode</td>
                <td class = 'no-wrap'>$itemAttributes</td>
                <td class = 'numeric-alignment'>$totalQty</td>
                <td class = 'numeric-alignment'>$rate</td>
                <td class = 'no-wrap'>$deliveryDate</td>
                <td class = 'no-wrap text-danger'>$errors</td>
                </tr>
                ";
            } else {
                $successRecords += 1;
                $validUI .= "
                <tr>
                <td class = 'no-wrap'>$itemCode</td>
                <td class = 'no-wrap'>$uomCode</td>
                <td class = 'no-wrap'>$itemAttributes</td>
                <td class = 'numeric-alignment'>$totalQty</td>
                <td class = 'numeric-alignment'>$rate</td>
                <td class = 'no-wrap'>$deliveryDate</td>
                </tr>
                ";
            }
        }
        return [
            'valid_records' => $successRecords,
            'invalid_records' => $failedRecords,
            'validUI' => $validUI,
            'invalidUI' => $invalidUI 
        ];
        
    }
}