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
use App\Models\ErpCustomerSaleSummary;
use App\Models\ErpFinancialYear;
use App\Models\ErpInvoiceItem;
use App\Models\ErpInvoicePaymentTerm;
use App\Models\ErpItemAttribute;
use App\Models\ErpProductionSlip;
use App\Models\ErpPslipItem;
use App\Models\ErpPslipItemDetail;
use App\Models\ErpSaleInvoice;
use App\Models\ErpSaleInvoiceHistory;
use App\Models\ErpSaleOrder;
use App\Models\ErpSaleOrderTed;
use App\Models\ErpSaleReturn;
use App\Models\ErpSaleReturnHistory;
use App\Models\ErpSoDynamicField;
use App\Models\ErpSoItem;
use App\Models\ErpSoItemAttribute;
use App\Models\ErpSoItemBom;
use App\Models\ErpSoItemDelivery;
use App\Models\ErpSoPaymentTerm;
use App\Models\ErpStore;
use App\Models\ErpTransportInvoice;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Organization;
use App\Models\OrganizationBookParameter;
use App\Models\PaymentTermDetail;
use App\Models\Unit;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Psy\TabCompletion\Matcher\ConstantsMatcher;
use stdClass;

class SaleModuleHelper  
{ 
    const SALES_RETURN_DEFAULT_TYPE = "sale-return";
    const SALES_INVOICE_DEFAULT_TYPE = "si";
    const SALES_INVOICE_DN_TYPE = "dnote";
    const SALES_INVOICE_DN_CUM_INV_TYPE = "si-dnote";
    const SALES_INVOICE_LEASE_TYPE = "lease-invoice";
    const SALES_INVOICE_TRANSPORTER_TYPE = "ti";
    const ORDER_TYPE_DEFAULT = "Order";
    const ORDER_TYPE_JOB_WORK = "Job Work";
    const ORDER_TYPE_SUB_CONTRACTING = "Sub Contracting";
    const ORDER_TYPES = [
        self::ORDER_TYPE_DEFAULT,
        self::ORDER_TYPE_JOB_WORK,
        self::ORDER_TYPE_SUB_CONTRACTING
    ];
    public static function getSoImports(): array
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
        } else if ($type === self::SALES_INVOICE_DN_TYPE) {
            return "Delivery Note";
        } else if ($type === self::SALES_INVOICE_DN_CUM_INV_TYPE) {
            return "Invoice cum DN";
        } else if ($type === self::SALES_INVOICE_LEASE_TYPE) {
            return "Lease Invoice";
        } else if ($type === ConstantHelper::SERVICE_INV_SERVICE_ALIAS) {
            return "Service Invoice";
        } else if ($type === self::SALES_INVOICE_TRANSPORTER_TYPE) {
            return "Transporter Invoice";
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
        // $bookLevelTaxParam = ServiceParametersHelper::getBookLevelParameterValue(ServiceParametersHelper::TAX_REQUIRED_PARAM, $bookId)['data'];
        // if (in_array("no", $bookLevelTaxParam) || count($bookLevelTaxParam) == 0) {
        //     return false;
        // }
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
        } else if ($type === self::SALES_INVOICE_TRANSPORTER_TYPE) {
            return [ConstantHelper::TI_SERVICE_ALIAS];
        }else {
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

    public static function cashCustomerMasterData(ErpSaleOrder|ErpSaleInvoice|ErpTransportInvoice $saleOrder)
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

    public static function getItemSellingPrice(Item $item, int|null $uomId = null) : float
    {
        $itemSellPrice = $item -> sell_price;
        if ($item -> uom_id === $uomId) {
            return floatval($itemSellPrice);
        } else {
            $sellPrice = 0;
            foreach ($item -> alternateUOMs as $altUom) {
                if ($altUom -> unit_id === $uomId) {
                    $sellPrice = $itemSellPrice * $altUom -> conversion_to_inventory;
                    break;
                }
            }
            return floatval($sellPrice);
        }
    }

    public static function getJoOrderTypeFromSoOrderType(string $soOrderType)
    {
        if ($soOrderType === self::ORDER_TYPE_SUB_CONTRACTING) {
            return ConstantHelper::TYPE_SUBCONTRACTING;
        } else {
            return $soOrderType;
        }
    }

    public static function updateOrCreateSoPaymentTerms(int $soHeaderId, int $paymentTermId, $creditDays)
    {
        //Get the payment terms data
        $paymentTermDetails = PaymentTermDetail::where('payment_term_id',$paymentTermId)->get();
        //Return if no record is found
        if ($paymentTermDetails->isEmpty()) {
            return;
        }
        //Array to keep track of inserted records
        $soPaymentTermIds = [];
        //Update or save the data 
        foreach($paymentTermDetails as $paymentTermDetail){
            $soPaymentTerm = ErpSoPaymentTerm::updateOrCreate(
                [
                    'so_header_id' => $soHeaderId,
                    'payment_term_id' => $paymentTermDetail->payment_term_id,
                    'payment_term_detail_id' => $paymentTermDetail->id,
                ],
                [
                    'trigger_type' => $paymentTermDetail->trigger_type,
                    'credit_days' => $paymentTermDetail->trigger_type == ConstantHelper::POST_DELIVERY ? ($creditDays ? $creditDays : 0) : 0,
                    'percent' => $paymentTermDetail -> percent
                ]
            );
            array_push($soPaymentTermIds, $soPaymentTerm -> id);
        }
        //Delete the records no longer required (of current so_header_id)
        ErpSoPaymentTerm::where('so_header_id', $soHeaderId) -> whereNotIn('id', $soPaymentTermIds) -> delete();
    }

    public static function updateOrCreateInvoicePaymentTerms(int $invoiceHeaderId, string $headerDocumentDate, int $paymentTermId, $creditDays)
    {
        //Get the payment terms data
        $paymentTermDetails = PaymentTermDetail::where('payment_term_id',$paymentTermId)->get();
        //Return if no record is found
        if ($paymentTermDetails->isEmpty()) {
            return;
        }
        //Array to keep track of inserted records
        $soPaymentTermIds = [];
        //Due Date
        $dueDate = $headerDocumentDate;
        $creditDueDate = $headerDocumentDate;
        if ($creditDays && $creditDays > 0) {
            $parsedDocumentDate = Carbon::parse($headerDocumentDate);
            $creditDueDate = $parsedDocumentDate -> addDays($creditDays) -> format('Y-m-d');
        }
        //Update or save the data 
        foreach($paymentTermDetails as $paymentTermDetail){
            $soPaymentTerm = ErpInvoicePaymentTerm::updateOrCreate(
                [
                    'invoice_header_id' => $invoiceHeaderId,
                    'payment_term_id' => $paymentTermDetail->payment_term_id,
                    'payment_term_detail_id' => $paymentTermDetail->id,
                ],
                [
                    'trigger_type' => $paymentTermDetail->trigger_type,
                    'credit_days' => $paymentTermDetail->trigger_type == ConstantHelper::POST_DELIVERY ? ($creditDays ? $creditDays : 0) : 0,
                    'percent' => $paymentTermDetail -> percent,
                    'due_date' => $paymentTermDetail->trigger_type == ConstantHelper::POST_DELIVERY ? $creditDueDate : $dueDate
                ]
            );
            array_push($soPaymentTermIds, $soPaymentTerm -> id);
        }
        //Delete the records no longer required (of current so_header_id)
        ErpInvoicePaymentTerm::where('invoice_header_id', $invoiceHeaderId) -> whereNotIn('id', $soPaymentTermIds) -> delete();
    }

    public static function buildCustomerSaleInvoiceSummary(ErpSaleInvoice $saleInvoice, ErpFinancialYear $fyYear, ErpSaleInvoiceHistory|null $oldSaleInvoice = null)
    {
        //Only run for approved documents and SI, SI-DNOTE
        $requiredStatuses = [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED];
        $requiredDocumentTypes = [ConstantHelper::SI_SERVICE_ALIAS, ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS];
        if (!in_array($saleInvoice -> document_status, $requiredStatuses) || !in_array($saleInvoice -> document_type, $requiredDocumentTypes)) {
            return;
        }
        //Create or update the summary
        $customerSaleSummary = ErpCustomerSaleSummary::firstOrCreate([
            'group_id' => $saleInvoice -> group_id,
            'company_id' => $saleInvoice -> company_id,
            'organization_id' => $saleInvoice -> organization_id,
            'customer_id' => $saleInvoice -> customer_id,
            'fy_id' => $fyYear -> id,
            'currency_id' => $saleInvoice -> org_currency_id
        ]);
        $customerSaleSummary -> fy_code = $fyYear -> alias;
        //Default to current invoice value to be incremented
        $newInvoiceValue = $saleInvoice -> total_item_value - $saleInvoice -> total_discount_value;
        $incrementInvoiceValue = $newInvoiceValue;
        //Update - Amend
        if ($oldSaleInvoice) {
            //Keep the difference
            $oldSaleInvoice = $oldSaleInvoice -> total_item_value - $oldSaleInvoice -> total_discount_value;
            $incrementInvoiceValue = $newInvoiceValue - $oldSaleInvoice;
        }
        //Increment the value or difference
        $customerSaleSummary -> increment('total_invoice_value', $incrementInvoiceValue);
    }

    public static function buildCustomerSaleReturnSummary(ErpSaleReturn $saleInvoice, ErpFinancialYear $fyYear, ErpSaleReturnHistory|null $oldSaleInvoice = null)
    {
        //Only run for approved documents and SI, SI-DNOTE
        $requiredStatuses = [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::POSTED];
        if (!in_array($saleInvoice -> document_status, $requiredStatuses)) {
            return;
        }
        //Create or update the summary
        $customerSaleSummary = ErpCustomerSaleSummary::firstOrCreate([
            'group_id' => $saleInvoice -> group_id,
            'company_id' => $saleInvoice -> company_id,
            'organization_id' => $saleInvoice -> organization_id,
            'customer_id' => $saleInvoice -> customer_id,
            'fy_id' => $fyYear -> id,
            'currency_id' => $saleInvoice -> org_currency_id
        ]);
        $customerSaleSummary -> fy_code = $fyYear -> alias;
        //Default to current invoice value to be incremented
        $newInvoiceValue = $saleInvoice -> total_item_value - $saleInvoice -> total_discount_value;
        $incrementInvoiceValue = $newInvoiceValue;
        //Update - Amend
        if ($oldSaleInvoice) {
            //Keep the difference
            $oldSaleInvoice = $oldSaleInvoice -> total_item_value - $oldSaleInvoice -> total_discount_value;
            $incrementInvoiceValue = $newInvoiceValue - $oldSaleInvoice;
        }
        //Increment the value or difference
        $customerSaleSummary -> increment('total_return_value', $incrementInvoiceValue);
    }
}