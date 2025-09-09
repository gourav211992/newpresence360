<?php

namespace App\Http\Controllers\API\Integration;

use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Helpers\SaleModuleHelper;
use App\Helpers\TaxHelper;
use App\Http\Controllers\Controller;
use App\Services\Integration\ConsigneeService;
use App\Http\Requests\Integration\ConsigneeRequest;
use App\Models\Customer;
use App\Models\ERP\ErpExternalIntegration;
use App\Models\ErpAddress;
use App\Models\ErpSaleOrder;
use App\Models\ErpSaleOrderTed;
use App\Models\ErpSoItem;
use App\Models\ErpSoItemDelivery;
use App\Models\ErpStore;
use App\Models\Item;

class FurlencoController extends Controller
{
    public function __construct(private ConsigneeService $service) {}

    public function consigneeStoreOrUpdate(ConsigneeRequest $request)
    {
        $results = $this->service->storeOrUpdate(
            $request->organization_id,
            $request->consignees
        );

        return [
            "message" => "Consignees created successfully.",
            "data" => $results
        ];
    }

    public function createSaleOrders(TripSaleOrderRequest $request)
    {
        try {
            //Auth credentials
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;

            $erpExternalIntegration = ErpExternalIntegration::with(
                ['book: id, book_code', 'book_name']
            )->whereorganizationId($organizationId)->first();

            if(!$erpExternalIntegration) {
                return response()->json([
                    'message' => 'Integration mapping not found.'
                ], 422);
            }

            $store = ErpStore::with('address')->find($erpExternalIntegration->store_id);

            //Store Address
            if (!$store || !isset($store->address)) {
                return response() -> json([
                    'message' => 'Store address not assigned.',
                    'error' => ''
                ], 422);
            }

            $customer = Customer::find($erpExternalIntegration->customer_id);

            if(!$customer) {
                return response()->json([
                    'message' => 'No customer found.'
                ], 422);
            }

            //Tax Country and State

            $bookId = $erpExternalIntegration->book_id;
            $bookCode = $erpExternalIntegration->book?->book_code??null;
            $companyStateId = null;
            $companyCountryId = null;

            if ($store && isset($store->address)) {
                $companyStateId = $store->address?->state_id??null;
                $companyCountryId = $store->address?->country_id??null;
            }

            $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($customer->currency_id, $request->date);
            if ($currencyExchangeData['status'] == false) {
                return response()->json([
                    'message' => $currencyExchangeData['message'],
                    'error' => "CurrencyExchangeRates",
                ], 422);
            }

            $itemTaxIds = [];
            $itemAttributeIds = [];


            $documentnumber = $request->trip_id;
            $regeneratedDocExist = ErpSaleOrder::withDefaultGroupCompanyOrg()-> where('book_id', $bookId)
                ->where('document_number',$documentnumber)->first();
                //Again check regenerated doc no
                if ($regeneratedDocExist) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "DUPLICATE_DOCUMENT_NUMBER",
                    ], 422);
                }

            $saleOrder = null;

            $customerId = $customer -> id ?? null;
            $customerPhoneNo = $customer -> mobile ?? null;
            $customerEmail = $customer -> email ?? null;
            $customerGSTIN = $customer -> compliances ?-> gstin_no ?? null;
            $customerCurrenyCode = $customer -> currency ?-> short_name ?? null;
            $customerPaymentTermCode = $customer -> paymentTerm ?-> alias ?? null;

            DB::beginTransaction();

            //Create
            $saleOrder = ErpSaleOrder::create([
                'organization_id' => $organizationId,
                'group_id' => $groupId,
                'company_id' => $companyId,
                'book_id' => $bookId,
                'book_code' => $bookCode,
                'document_type' => ConstantHelper::SO_SERVICE_ALIAS,
                'document_number' => $documentnumber,
                'document_date' => $request->date,
                'revision_number' => 0,
                'revision_date' => null,
                'order_type' => SaleModuleHelper::ORDER_TYPE_DEFAULT,
                'reference_number' => null,
                'store_id' => $store ?-> id ?? null,
                'store_code' => $store ?-> store_name ?? null,

                'customer_id' => $customerId,
                'customer_email' => $customerEmail,
                'customer_phone_no' => $customerPhoneNo,
                'customer_gstin' => $customerGSTIN,
                'customer_code' => $customer->customer_code,
                'billing_address' => null,
                'shipping_address' => null,
                'currency_id' => $customer->currency_id,
                'currency_code' => $customerCurrenyCode,
                'payment_term_id' => $customer->payment_terms_id,
                'payment_term_code' => $customerPaymentTermCode,
                'credit_days' => $customer -> credit_days ?? 0,
                'document_status' => ConstantHelper::APPROVAL_NOT_REQUIRED,
                'approval_level' => 1,
                'remarks' => 'Order pushed through API',
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

            //Billing Address
            $customerBillingAddress = ErpAddress::find($request -> billing_address);
            if ($customerBillingAddress) {
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
                $billingAddress = $saleOrder -> billing_address_details() -> create([
                    'address' => $request -> new_billing_address,
                    'country_id' => $request -> new_billing_country_id,
                    'state_id' => $request -> new_billing_state_id,
                    'city_id' => $request -> new_billing_city_id,
                    'type' => 'billing',
                    'pincode' => $request -> new_billing_pincode,
                    'phone' => $request -> new_billing_phone,
                    'fax_number' => null
                ]);
            }
            // Shipping Address
            $customerShippingAddress = ErpAddress::find($request -> shipping_address);
            if ($customerShippingAddress) {
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
                $shippingAddress = $saleOrder -> shipping_address_details() -> create([
                    'address' => $request -> new_shipping_address,
                    'country_id' => $request -> new_shipping_country_id,
                    'state_id' => $request -> new_shipping_state_id,
                    'city_id' => $request -> new_shipping_city_id,
                    'type' => 'shipping',
                    'pincode' => $request -> new_shipping_pincode,
                    'phone' => $request -> new_shipping_phone,
                    'fax_number' => null
                ]);
            }

            $saleOrder -> location_address_details() -> create([
                'address' => $store -> address -> address,
                'country_id' => $store -> address -> country_id,
                'state_id' => $store -> address -> state_id,
                'city_id' => $store -> address -> city_id,
                'type' => 'location',
                'pincode' => $store -> address -> pincode,
                'phone' => $store -> address -> phone,
                'fax_number' => $store -> address -> fax_number
            ]);


            //Get Header Discount
            $totalHeaderDiscount = 0;
            $totalHeaderDiscountArray = [];

            //Initialize item discount to 0
            $itemTotalDiscount = 0;
            $itemTotalValue = 0;
            $totalTax = 0;
            $totalItemValueAfterDiscount = 0;

            $saleOrder -> billing_address = isset($billingAddress) ? $billingAddress -> id : null;
            $saleOrder -> shipping_address = isset($shippingAddress) ? $shippingAddress -> id : null;
            $saleOrder -> save();
            //Seperate array to store each item calculation
            $itemsData = array();

            if(!count($request->skus)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'SKU not found.',
                    'error' => "",
                ], 422);
            }

            //Items
            $totalValueAfterDiscount = 0;
            foreach ($request->skus as $key => $sku) {
                $itemQty = $sku['item_qty'];
                $itemRate = $sku['item_rate'];

                $item = Item::where('item_code', $sku['item_code'])->first();

                if(!$item) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "No item found with item code: {$sku['item_code']}",
                        'error' => "",
                    ], 422);
                }

                //Check if Item Bom Exists
                $bomDetails = ItemHelper::checkItemBomExists($item->id, []);
                if (!isset($bomDetails['bom_id'])) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "No BOM found with item code: {$sku['s']}",
                        'error' => "",
                    ], 422);
                }

                $itemValue = ($itemQty * $itemRate);
                $itemDiscount = 0;
                $valueAfterHeaderDiscount = 0;

                $itemTotalValue += $itemValue;
                $itemTotalDiscount += $itemDiscount;
                $itemValueAfterDiscount = $itemValue - $itemDiscount;
                $totalValueAfterDiscount += $itemValueAfterDiscount;
                $totalItemValueAfterDiscount += $itemValueAfterDiscount;


                $itemTax = 0;
                $taxDetails = SaleModuleHelper::checkTaxApplicability($customerId, $bookId) ? TaxHelper::calculateTax($item->hsn_id, $itemRate, $companyCountryId, $companyStateId, $partyCountryId ?? $shippingAddress->country_id, $partyStateId ?? $shippingAddress->state_id, 'sale') : [];
                if (isset($taxDetails) && count($taxDetails) > 0) {
                    foreach ($taxDetails as $taxDetail) {
                        $itemTax += ((double)$taxDetail['tax_percentage'] / 100 * $itemValue);
                    }
                    if($taxDetail['applicability_type']=="collection")
                    {
                        $totalTax += $itemTax;
                    }
                    else
                    {
                        $totalTax -= $itemTax;
                    }
                }

                $itemsData = [
                    'sale_order_id' => $saleOrder -> id,
                    'bom_id' => $bomDetails['bom_id'],
                    'item_id' => $item -> id,
                    'item_code' => $item -> item_code,
                    'item_name' => $item -> item_name,
                    'hsn_id' => $item -> hsn_id,
                    'hsn_code' => $item -> hsn ?-> code,
                    'uom_id' => $item->uom_id,
                    'uom_code' => $item -> uom ?-> name,
                    'order_qty' => $itemQty,
                    'inventory_uom_qty' => $itemQty,
                    'invoice_qty' => 0,
                    'inventory_uom_id' => $item -> uom ?-> id,
                    'inventory_uom_code' => $item -> uom ?-> name,
                    'rate' => $itemRate,
                    'delivery_date' => $request->date,
                    'item_discount_amount' => $itemDiscount,
                    'header_discount_amount' => 0,
                    'item_expense_amount' => 0, //Need to change
                    'header_expense_amount' => 0, //Need to change
                    'tax_amount' => $itemTax,
                    'total_item_amount' => ($itemValue + $itemTax),
                    'company_currency_id' => null,
                    'company_currency_exchange_rate' => null,
                    'group_currency_id' => null,
                    'group_currency_exchange_rate' => null,
                    'remarks' => 'Order pushed by API',
                ];

                $soItem = ErpSoItem::create($itemsData);

                //TED Data (TAX)
                if (isset($taxDetails) && count($taxDetails) > 0) {
                    foreach ($taxDetails as $taxDetail) {
                        $soItemTedForDiscount = ErpSaleOrderTed::updateOrCreate(
                            [
                                'sale_order_id' => $saleOrder -> id,
                                'so_item_id' => $soItem -> id,
                                'ted_type' => 'Tax',
                                'ted_level' => 'D',
                                'ted_id' => $taxDetail['id'],
                            ],
                            [
                                'ted_group_code' => $taxDetail['tax_group'],
                                'ted_name' => $taxDetail['tax_type'],
                                'assessment_amount' => $valueAfterHeaderDiscount,
                                'ted_percentage' => (double)$taxDetail['tax_percentage'],
                                'ted_amount' => ((double)$taxDetail['tax_percentage'] / 100 * $valueAfterHeaderDiscount),
                                'applicable_type' => $taxDetail['applicability_type'],
                            ]
                        );
                        array_push($itemTaxIds,$soItemTedForDiscount -> id);
                    }
                }

                //Item Attributes
                if (isset($sku->item_attributes)) {
                    $attributesArray = json_decode($request -> item_attributes[$itemDataKey], true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($attributesArray)) {
                        foreach ($attributesArray as $attributeKey => $attribute) {
                            $attributeVal = "";
                            $attributeValId = null;
                            foreach ($attribute['values_data'] as $valData) {
                                if ($valData['selected']) {
                                    $attributeVal = $valData['value'];
                                    $attributeValId = $valData['id'];
                                    break;
                                }
                            }
                            $itemAttribute = ErpSoItemAttribute::updateOrCreate(
                                [
                                    'sale_order_id' => $saleOrder -> id,
                                    'so_item_id' => $soItem -> id,
                                    'item_attribute_id' => $attribute['id'],
                                ],
                                [
                                    'item_code' => $soItem -> item_code,
                                    'attribute_name' => $attribute['group_name'],
                                    'attr_name' => $attribute['attribute_group_id'],
                                    'attribute_value' => $attributeVal,
                                    'attr_value' => $attributeValId,
                                ]
                            );
                            array_push($itemAttributeIds, $itemAttribute -> id);
                        }
                    } else {
                        return response() -> json([
                            'message' => 'Item No. ' . ($itemDataKey + 1) . ' has invalid attributes',
                            'error' => ''
                        ], 422);
                    }
                }

                //Item Deliveries

                $itemDeliveryRowData = [
                        'sale_order_id' => $saleOrder -> id,
                        'so_item_id' => $soItem -> id,
                        'ledger_id' => null,
                        'qty' => $soItem -> order_qty,
                        'invoice_qty' => 0,
                        'delivery_date' => $soItem -> delivery_date
                    ];
                ErpSoItemDelivery::updateOrCreate(['sale_order_id' => $saleOrder -> id, 'so_item_id' => $soItem -> id], $itemDeliveryRowData);


            }

            //Check all total values
            if ($itemTotalValue < 0)
            {
                DB::rollBack();
                return response() -> json([
                    'status' => 'error',
                    'message' => 'Document Value cannot be less than 0'
                ], 422);
            }

            $saleOrder->total_item_value = $itemTotalValue;
            $saleOrder->total_tax_value = $totalTax;
            $saleOrder->total_amount = ($itemTotalValue + $totalTax);

            if($saleOrder) {
                $attachments = $request->file('attachment');
                $revisionNumber = $saleOrder->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $modelName = get_class($saleOrder);
                $totalValue = $saleOrder->total_amount ?? 0;
                $approveDocument = Helper::approveDocument($saleOrder->book_id, $saleOrder->id, $revisionNumber , $saleOrder->remarks, $attachments, $saleOrder->approval_level, $actionType, $totalValue, $modelName);
                $saleOrder->document_status = $approveDocument['approvalStatus'] ?? $saleOrder->document_status;
            }

            $saleOrder->save();

            SaleModuleHelper::updateOrCreateSoPaymentTerms($saleOrder->id, $saleOrder->payment_term_id, $saleOrder->credit_days);

            DB::commit();
            return response() -> json([
                'message' => "Sale Order created successfully"
            ]);

        } catch(\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => 'Server Error',
                'exception' => $ex -> getMessage()
            ], 500);
        }
    }
}
