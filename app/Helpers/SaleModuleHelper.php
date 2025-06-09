<?php

namespace App\Helpers;
use App\Models\Attribute;
use App\Models\Bom;
use App\Models\BomDetail;
use App\Models\Book;
use App\Models\CashCustomerDetail;
use App\Models\Compliance;
use App\Models\Customer;
use App\Models\ErpAddress;
use App\Models\ErpAttribute;
use App\Models\ErpInvoiceItem;
use App\Models\ErpItemAttribute;
use App\Models\ErpProductionSlip;
use App\Models\ErpPslipItem;
use App\Models\ErpPslipItemDetail;
use App\Models\ErpSaleInvoice;
use App\Models\ErpSaleOrder;
use App\Models\ErpSaleOrderTed;
use App\Models\ErpSoItem;
use App\Models\ErpSoItemAttribute;
use App\Models\ErpSoItemBom;
use App\Models\ErpSoItemDelivery;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Organization;
use App\Models\OrganizationBookParameter;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Psy\TabCompletion\Matcher\ConstantsMatcher;
use stdClass;

class SaleModuleHelper  
{ 
    const SALES_RETURN_DEFAULT_TYPE = "sale-return";
    const SALES_INVOICE_DEFAULT_TYPE = "sale-invoice";
    const SALES_INVOICE_LEASE_TYPE = "lease-invoice"
    ;public static function getSoImports(): array
    {
        return [
            'v1' => asset('templates/SalesOrderImportV1.xlsx'),
        ];
    }
    public static function getAndReturnInvoiceType(string $type) : string
    {
        $invoiceType = isset($type) && in_array($type, ConstantHelper::SALE_INVOICE_DOC_TYPES) ? $type : ConstantHelper::SI_SERVICE_ALIAS;
        return $invoiceType;
    }
    // public static function getAndReturnInvoiceTypeName(string $type) : string
    // {
    //     if ($type === ConstantHelper::SI_SERVICE_ALIAS) {
    //         return "Sales Invoice";
    //     } else if ($type === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
    //         return "Delivery Note";
    //     } else if ($type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS) {
    //         return "Invoice CUM Delivery Note";
    //     } else if ($type === ConstantHelper::LEASE_INVOICE_SERVICE_ALIAS) {
    //         return "Lease Invoice";
    //     } else {
    //         return "";
    //     }
    // }
    public static function getAndReturnInvoiceTypeName(string $type) : string
    {
        if ($type === self::SALES_INVOICE_DEFAULT_TYPE) {
            return "Invoice";
        } else if ($type === self::SALES_INVOICE_LEASE_TYPE) {
            return "Lease Invoice";
        } else {
            return "";
        }
    }

    public static function getAndReturnReturnType(string $type) : string
    {
        $returnType = isset($type) && in_array($type, ConstantHelper::SALE_RETURN_DOC_TYPES_FOR_DB) ? $type : ConstantHelper::SR_SERVICE_ALIAS;
        return $returnType;
    }
    public static function getAndReturnReturnTypeName(string $type) : string
    {
        if ($type == self::SALES_RETURN_DEFAULT_TYPE) {
            return "Return";
        }  else {
            return "";
        }
    }

    public static function checkTaxApplicability(int $customerId, int $bookId) : bool
    {
        //Book Level Tax
        $bookLevelTaxParam = ServiceParametersHelper::getBookLevelParameterValue(ServiceParametersHelper::TAX_REQUIRED_PARAM, $bookId)['data'];
        if (in_array("no", $bookLevelTaxParam) || count($bookLevelTaxParam) == 0) {
            return false;
        }
        //Customer Level Tax
        // $customerLevelTaxParam = Compliance::where('morphable_type', Customer::class) -> where('morphable_id', $customerId) -> first();
        // if (!isset($customerLevelTaxParam)) {
        //     return false;
        // }
        // if (!$customerLevelTaxParam -> gst_applicable) {
        //     return false;
        // }
        return true;
    }

    public static function item_attributes_array(int $itemId, array $selectedAttributes)
    {
        if (isset($itemId)) {
            $itemAttributes = ErpItemAttribute::where('item_id', $itemId) -> get();
        } else {
            $itemAttributes = [];
        }
        $processedData = [];
        foreach ($itemAttributes as $attribute) {
            $existingAttribute = array_filter($selectedAttributes, function ($selectedAttr) use($attribute) {
                return $selectedAttr['item_attribute_id'] == $attribute -> id;
            });
            // $existingAttribute = ErpSoItemAttribute::where('so_item_id', $this -> getAttribute('id')) -> where('item_attribute_id', $attribute -> id) -> first();
            if (!isset($existingAttribute) || count($existingAttribute) == 0) {
                continue;
            }
            $existingAttribute = array_values($existingAttribute);
            $attributesArray = array();
            $attribute_ids = $attribute -> attribute_id ? json_decode($attribute -> attribute_id) : [];
            $attribute -> group_name = $attribute -> group ?-> name;
            foreach ($attribute_ids as $attributeValue) {
                    $attributeValueData = ErpAttribute::where('id', $attributeValue) -> select('id', 'value') -> where('status', 'active') -> first();
                    if (isset($attributeValueData))
                    {
                        $isSelected = $existingAttribute[0]['value_id'] == $attributeValueData -> id;
                        $attributeValueData -> selected = $isSelected ? true : false;
                        array_push($attributesArray, $attributeValueData);
                    }
                
            }
           $attribute -> values_data = $attributesArray;
           $attribute = $attribute -> only(['id','group_name', 'values_data', 'attribute_group_id']);
           array_push($processedData, ['id' => $attribute['id'], 'group_name' => $attribute['group_name'], 'values_data' => $attributesArray, 'attribute_group_id' => $attribute['attribute_group_id']]);
        }
        $processedData = collect($processedData);
        return $processedData;
    }

    public static function sortByDueDateLogic(Collection $collection, string $dueDateKey = 'due_date')
    {
        // Use the current date if not provided
        $currentDate = date('Y-m-d');

        // Use the current date if not provided
        $currentDate = $currentDate ?? now()->toDateString();

        return $collection->sort(function ($a, $b) use ($currentDate, $dueDateKey) {
            $dateA = $a -> {$dueDateKey};
            $dateB = $b -> {$dueDateKey};

            // Determine if dates are overdue or upcoming
            $isOverdueA = ($dateA < $currentDate);
            $isOverdueB = ($dateB < $currentDate);

            // Priority: Overdue dates first
            if ($isOverdueA && !$isOverdueB) {
                return -1; // $a comes before $b
            } elseif (!$isOverdueA && $isOverdueB) {
                return 1; // $b comes before $a
            }

            // If both are overdue or both are upcoming
            if ($isOverdueA && $isOverdueB) {
                // Overdue: Largest difference first
                return strtotime($dateB) - strtotime($dateA);
            } else {
                // Upcoming: Smallest difference first
                return strtotime($dateA) - strtotime($dateB);
            }
        })->values(); // Re-index the collection
    }

    public static function checkInvoiceDocTypesFromUrlType(string $type) : array
    {
        if ($type === self::SALES_INVOICE_DEFAULT_TYPE){
            return [ConstantHelper::SI_SERVICE_ALIAS, ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS];
        } else if ($type === self::SALES_INVOICE_LEASE_TYPE) {
            return [ConstantHelper::LEASE_INVOICE_SERVICE_ALIAS];
        } else {
            return [];
        }
    }

    public static function getServiceName($bookId)
    {
        $book = Book::find($bookId);
        if (isset($book)) {
            if ($book -> service ?-> alias === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
                $invoiceToFollowParam = OrganizationBookParameter::where('book_id', $book -> id) -> where('parameter_name', ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM) -> first();
                if (isset($invoiceToFollowParam) && $invoiceToFollowParam -> parameter_value[0] == 'yes') {
                    return $book -> service -> name;
                } else {
                    return "DN cum Invoice";
                }
            } else {
                return $book -> service -> name;
            }
        } else {
            return "N/A";
        }
    }

    public static function reCalculateExpenses(array $itemDetails, $referenceFromType = ConstantHelper::SO_SERVICE_ALIAS) : array
    {
        //Assign empty expense
        $expensesDetails = [];
        foreach ($itemDetails as $itemDetail) {
            //Loop through all item reference IDs
            foreach ($itemDetail['reference_from'] as $referenceItem) {
                //Find the SO Item and it's header
                if ($referenceFromType == ConstantHelper::SO_SERVICE_ALIAS) {
                    $referenceItemDetail = ErpSoItem::find($referenceItem);
                    $referenceHeaderDetail = ErpSaleOrder::find($referenceItemDetail ?-> sale_order_id);
                } else {
                    $referenceItemDetail = ErpInvoiceItem::find($referenceItem);
                    $referenceHeaderDetail = ErpSaleInvoice::find($referenceItemDetail ?-> sale_invoice_id);
                }
                //Calculate the net rate for expense
                $totalValueAfterDiscount = ($itemDetail['item_qty'] * $itemDetail['rate']) - $itemDetail['header_discount'] - $itemDetail['item_discount'];
                $totalNetRate = $totalValueAfterDiscount / $itemDetail['item_qty'];
                if (isset($referenceHeaderDetail) && $referenceHeaderDetail -> expense_ted) {
                    //Loop through all the expenses stored in header level
                    foreach ($referenceHeaderDetail -> expense_ted as $headerExpense) {
                        //Calculate ted percentage from amount and apply it to item
                        $headerExpensePercentage = ($headerExpense -> ted_amount / $headerExpense -> assessment_amount) * 100;
                        $itemExpense = $totalNetRate * $headerExpensePercentage;
                        //Check if expense already exists in total expense
                        $existingExpenseIndex = null;
                        foreach ($expensesDetails as $expensesDetailIndex => $expensesDetail) {
                            if ($expensesDetail['id'] == $headerExpense -> id) {
                                $existingExpenseIndex = $expensesDetailIndex;
                                break;
                            }
                        }
                        //existing expense found
                        if (isset($existingExpenseIndex)) {
                            $expensesDetails[$existingExpenseIndex]['ted_amount'] += $itemExpense;
                        } else { //Append new Expense
                            array_push($expensesDetail, [
                                'id' => $headerExpense -> id,
                                'ted_amount' => $itemExpense,
                                'ted_name' => $headerExpense -> ted_name,
                                'ted_percentage' => $headerExpense -> ted_percentage
                            ]);
                        }
                    }
                }
            }
        }
        return $expensesDetails;
    }

    public static function getPendingPackingSlipsOfOrder(int $soItemId)
    {
        $pslipItemIds = ErpPslipItem::where('so_item_id', $soItemId) -> get() -> pluck('id') -> toArray();
        $pslipIds = ErpPslipItemDetail::whereIn('pslip_item_id', $pslipItemIds) -> whereNull('dn_item_id') -> get() -> pluck('pslip_id') -> toArray();
        $pslips = ErpProductionSlip::whereIn('id', $pslipIds) -> get();
        $pslipNos = "";
        foreach ($pslips as $pslipIndex => $pslip) {
            $pslipNos .= ($pslipIndex == 0 ? '' : ',') . $pslip -> book_code . '-' . $pslip -> document_number;
        }
        return $pslipNos;
    }

    public static function updateEInvoiceDataFromHelper(Model $document, bool $invoiceTypeField = true) : Model
    {
        //Update Organization Address
        if ($invoiceTypeField) {
            $organization = Organization::find($document -> organization_id);
            $actualOrgAddress = $organization -> addresses ?-> first();
            if (isset($actualOrgAddress)) {
                $document->organization_address()->updateOrCreate(
                    [
                        'type' => 'organization'
                    ],
                    [
                        'address' => $actualOrgAddress->address,
                        'country_id' => $actualOrgAddress->country_id,
                        'state_id' => $actualOrgAddress->state_id,
                        'city_id' => $actualOrgAddress->city_id,
                        'pincode' => $actualOrgAddress->pincode,
                        'phone' => $actualOrgAddress->phone,
                        'fax_number' => $actualOrgAddress->fax_number
                    ]
                );
            }
        }
        
        //Update Store Address
        $store = ErpStore::find($document -> store_id);
        $actualStoreAddress = $store -> address;
        if (isset($actualStoreAddress)) {
            $document->location_address_details()->updateOrCreate(
                [
                    'type' => 'location'
                ],
                [
                    'address' => $actualStoreAddress->address,
                    'country_id' => $actualStoreAddress->country_id,
                    'state_id' => $actualStoreAddress->state_id,
                    'city_id' => $actualStoreAddress->city_id,
                    'pincode' => $actualStoreAddress->pincode,
                    'phone' => $actualStoreAddress->phone,
                    'fax_number' => $actualStoreAddress->fax_number
                ]
            );
        }
        //Update Customer Billing Address
        $actualCustomerBillAddress = ErpAddress::find($document -> billing_address);
        if (isset($actualCustomerBillAddress)) {
            $document->billing_address_details()->updateOrCreate(
                [
                    'type' => 'billing'
                ],
                [
                    'address' => $actualCustomerBillAddress->address,
                    'country_id' => $actualCustomerBillAddress->country_id,
                    'state_id' => $actualCustomerBillAddress->state_id,
                    'city_id' => $actualCustomerBillAddress->city_id,
                    'pincode' => $actualCustomerBillAddress->pincode,
                    'phone' => $actualCustomerBillAddress->phone,
                    'fax_number' => $actualCustomerBillAddress->fax_number
                ]
            );
        }
        //Update Customer Shipping Address
        $actualCustomerShipAddress = ErpAddress::find($document -> shipping_address);
        if (isset($actualCustomerShipAddress)) {
            $document->shipping_address_details()->updateOrCreate(
                [
                    'type' => 'billing'
                ],
                [
                    'address' => $actualCustomerShipAddress->address,
                    'country_id' => $actualCustomerShipAddress->country_id,
                    'state_id' => $actualCustomerShipAddress->state_id,
                    'city_id' => $actualCustomerShipAddress->city_id,
                    'pincode' => $actualCustomerShipAddress->pincode,
                    'phone' => $actualCustomerShipAddress->phone,
                    'fax_number' => $actualCustomerShipAddress->fax_number
                ]
            );
        }
        //Retrieve Customer and update fields from there
        $customer = Customer::find($document -> customer_id);
        if (isset($customer) && $customer -> customer_type === ConstantHelper::REGULAR) {
            $document -> customer_phone_no = $customer -> mobile;
            $document -> customer_email = $customer -> email;
            $document -> customer_gstin = $customer -> compliances ?-> gstin_no;
        }
        if ($invoiceTypeField) {
            //Update GST Invoice
            $document -> gst_invoice_type = EInvoiceHelper::getGstInvoiceType($document -> customer_id, 
            $actualCustomerShipAddress ?-> country_id, $actualOrgAddress ?-> country_id);
        }
        
        //Save
        $document -> save();
        return $document;
    }

    public static function cashCustomerMasterData(ErpSaleOrder|ErpSaleInvoice $saleOrder)
    {
        $customer = Customer::find($saleOrder -> customer_id);
        if (!isset($customer) || (isset($customer) && $customer -> customer_type !== ConstantHelper::CASH)) {
            return;
        }
        $customerPhoneNo = $saleOrder -> customer_phone_no;
        $customerEmail = $saleOrder -> customer_email;
        $customerGstIn = $saleOrder -> customer_gstin;
        $customerName = $saleOrder -> consignee_name;
        $shippingAddress = $saleOrder -> shipping_address_details;
        $billingAddress = $saleOrder -> billing_address_details;

        //Check for existing record
        $existingPhoneRecord = CashCustomerDetail::where('phone_no', $customerPhoneNo) -> first();

        if (isset($existingPhoneRecord)) {
            $existingPhoneRecord -> name = $customerName;
            $existingPhoneRecord -> gstin = $customerGstIn;
            $existingPhoneRecord -> email = $customerEmail;
            $existingPhoneRecord -> save();

            $existingPhoneRecord -> shipping_address() -> create([
                'address' => $shippingAddress -> address,
                'country_id' => $shippingAddress -> country_id,
                'state_id' => $shippingAddress -> state_id,
                'city_id' => $shippingAddress -> city_id,
                'type' => 'shipping',
                'pincode' => $shippingAddress -> pincode,
                'phone' => $shippingAddress -> phone,
                'fax_number' => $shippingAddress -> fax_number
            ]);
            $existingPhoneRecord -> billing_address() -> create([
                'address' => $billingAddress -> address,
                'country_id' => $billingAddress -> country_id,
                'state_id' => $billingAddress -> state_id,
                'city_id' => $billingAddress -> city_id,
                'type' => 'billing',
                'pincode' => $billingAddress -> pincode,
                'phone' => $billingAddress -> phone,
                'fax_number' => $billingAddress -> fax_number
            ]);
        } else {
            $cashCustomer = CashCustomerDetail::create([
                'customer_id' => $saleOrder -> customer_id,
                'name' => $customerName,
                'email' => $customerEmail,
                'phone_no' => $customerPhoneNo,
                'gstin' => $customerGstIn
            ]);
            $cashCustomer -> shipping_address() -> create([
                'address' => $shippingAddress -> address,
                'country_id' => $shippingAddress -> country_id,
                'state_id' => $shippingAddress -> state_id,
                'city_id' => $shippingAddress -> city_id,
                'type' => 'shipping',
                'pincode' => $shippingAddress -> pincode,
                'phone' => $shippingAddress -> phone,
                'fax_number' => $shippingAddress -> fax_number
            ]);
            $cashCustomer -> billing_address() -> create([
                'address' => $billingAddress -> address,
                'country_id' => $billingAddress -> country_id,
                'state_id' => $billingAddress -> state_id,
                'city_id' => $billingAddress -> city_id,
                'type' => 'billing',
                'pincode' => $billingAddress -> pincode,
                'phone' => $billingAddress -> phone,
                'fax_number' => $billingAddress -> fax_number
            ]);
            
            
        }
    }

    public static function shufabImportDataSave(\Illuminate\Database\Eloquent\Collection $data, int $bookId, int $locationId, $user, string $document_status) : array
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
                $customerAddresses = $customer -> addresses();
                $customerBillingAddress = $customerAddresses -> where('is_billing', 1) -> first();
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
                $customerShippingAddress =$customerAddresses -> where('is_shipping', 1) -> first();
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
                    $taxDetails = self::checkTaxApplicability($customer ?-> id, $bookId) ? 
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
}