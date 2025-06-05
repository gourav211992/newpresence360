<?php

namespace App\Helpers;
use App\Models\AlternateUOM;
use App\Models\AuthUser;
use App\Models\Bom;
use App\Models\CustomerItem;
use App\Models\ErpRateContract;
use App\Models\Item;
use App\Models\VendorItem;
use App\Models\Vendor;
use App\Models\ErpAddress;
use App\Helpers\CurrencyHelper;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\OrganizationGroup;
use stdClass;

class ItemHelper  
{ 
    /* array : $itemAttributes should be in the form -> [['attribute_id' => 1, 'attribute_value' => 10]] */
    public static function checkItemBomExists(int $itemId, array $itemAttributes, $bomType = 'bom', $customerId = null) : array|null
    {
        $subType = null;
        $item = Item::find($itemId);
        //Item not found
        if (!isset($item)) {
            return array(
                'status' => 'item_not_found',
                'bom_id' => null,
                'message' => 'Item not found',
                'sub_type' => $subType,
                'customizable' => null
            );
        }
        //Check Item Sub Type
        $subType = self::getItemSubType($item->id);
        $subTypeStatus = false;
        if(in_array($subType, ['Finished Goods', 'WIP/Semi Finished'])) {
            $subTypeStatus = true;
        }
        
        if (!$subTypeStatus) {
            return array(
                'status' => 'bom_not_required',
                'bom_id' => null,
                'message' => 'BOM not required',
                'sub_type' => $subType,
                'customizable' => null
            );
        }
        //If Item is SEMI FINISHED OR FINISHED PRODUCT -> Check item level Bom
        $matchedBomId = null;
        $itemBoms = Bom::withDefaultGroupCompanyOrg()->where('bom_type', ConstantHelper::FIXED) -> where('item_id', $item -> id) 
        ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
        ->where(function($query) use($bomType, $customerId) {
            if($bomType == ConstantHelper::BOM_SERVICE_ALIAS) {
                $query->where('type', $bomType);
            } else {
                if($customerId) {
                    $query->where('customer_id', $customerId);
                }
            }
        })
        ->get();
        if (!isset($itemBoms) || count($itemBoms) == 0) {
            return array(
                'status' => 'bom_not_exists',
                'bom_id' => null,
                'message' => 'BOM does not exist',
                'sub_type' => $subType,
                'customizable' => null
            );
        }
        $matchedBomId = $itemBoms[0]->id ?? null;
        //Check if all atributes are selected
        $actualItemAttributes = $item -> itemAttributes;
        $attributes = array();
        foreach($actualItemAttributes as $currentAttribute) {
            if($currentAttribute?->required_bom) {
                array_push($attributes, $currentAttribute);
            }
        }
        //Compare all BOM with required BOM attribute values 
        if(count($attributes) > 0) {
            $matchedBomId = null;
            foreach ($itemBoms as $bom) {
                $attributeBomCreated = false;
                foreach ($bom -> bomAttributes as $attribute) {
                    $reqBomAttribute = array_filter($attributes, function ($reqAttribute) use($attribute) {
                        return $reqAttribute -> id == $attribute -> item_attribute_id;
                    });
                    if ($reqBomAttribute && count($reqBomAttribute) > 0) {
                        $matchingAttribute = array_filter($itemAttributes, function ($itemAttribute) use($attribute) {
                            return $itemAttribute['attribute_value'] == $attribute -> attribute_value && $itemAttribute['attribute_id'] == $attribute -> item_attribute_id;
                        });
                        if ($matchingAttribute && count($matchingAttribute) > 0) {
                            $attributeBomCreated = true;
                        } else {
                            $attributeBomCreated = false;
                            break;
                        }
                    }
                }
                if ($attributeBomCreated) {
                    $matchedBomId = $bom -> id;
                    break;
                }
            }
        }
        $matchedBom = $matchedBomId ? Bom::find($matchedBomId) : null;
        return array(
            'status' => $matchedBomId ? 'bom_exists' : 'bom_not_exists',
            'bom_id' => $matchedBomId,
            'message' => $matchedBomId ? 'Bom exist' : 'BOM does not exist',
            'sub_type' => $subType,
            'customizable' => $matchedBom ? $matchedBom -> customizable : null
        );
    }

    /*Created helper for the get created bom cost*/
    public static function getChildBomItemCost($itemId, $selectedAttributes = [])
    {
        $bomExist = self::checkItemBomExists($itemId,[]);
        if (!$bomExist['bom_id']) {
            return ['cost' => 0, 'status' => 422, 'message' => 'Not found header in BOM'];
        }
        $bom = Bom::where('id', $bomExist['bom_id'])->first();
        if ($bom) {
            $totalValue = $bom->total_value ?? 0;
            return ['cost' => $totalValue, 'route' => route('bill.of.material.edit', $bom->id), 'status' => 200, 'message' => 'Fetched BOM header item cost'];
        }
    }
    
    # Return item sub type name
    public static function getItemSubType($itemId = null)
    {
        $item = Item::find($itemId);
        $subTypes = $item?->subTypes ? $item?->subTypes : [];
        $name = null;
        $actualItemSubTypes = collect([]);
        foreach ($subTypes as $itemSubType) {
            $currentSubType = new stdClass();
            $currentSubType -> name = $itemSubType ?-> subType ?-> name;
            $actualItemSubTypes -> push($currentSubType);
        }

        $subType = collect($actualItemSubTypes)->whereIn('name',['Finished Goods'])->first();
        if($subType) {
            $name = $subType?->name;
        }

        if(!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name',['WIP/Semi Finished'])->first();
            if($subType) {
                $name = $subType?->name;
            }
        }

        if(!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name',['Raw Material'])->first();
            if($subType) {
                $name = $subType?->name;
            }
        }

        if(!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name',['Asset'])->first();
            if($subType) {
                $name = $subType?->name;
            }
        }

        if(!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name',['Expense'])->first();
            if($subType) {
                $name = $subType?->name;
            }
        }

        if(!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name',['Traded Item'])->first();
            if($subType) {
                $name = $subType?->name;
            }
        }
        
        return $name;
    } 

    # get item uom by item id   param :- item_id and uom_type [purchase, selling] return uomId
    public static function getItemUom($itemId, $uomType)
    {
        $item = Item::find($itemId);
        if (!$item) {
            return null; // Item not found
        }
        $altUom = $item?->uom_id;
        if($item?->alternateUOMs->count()) {
            if($uomType == 'purchase') {
                $altUom = $item->alternateUOMs()->where('is_purchasing',1)->first();
                $altUom = $altUom->id ?? null;
            }
            if($uomType == 'selling') {
                $altUom = $item->alternateUOMs()->where('is_selling',1)->first();
                $altUom = $altUom->id ?? null;
            }
        }    
        return $altUom;    
    }

    public static function getUserOrgDetails($user = null)
    {
        $user = $user ?? auth()->user() ?? Helper::getAuthenticatedUser();
        $organization = Organization::find($user->organization_id);
        $group = $organization?->group_id ? OrganizationGroup::find($organization->group_id) : null;
        $company = $organization?->company_id ? OrganizationCompany::find($organization->company_id) : null;
        $currency = $organization?->currency_id ? Currency::find($organization->currency_id) : null;
        return [
            'org_currency_id'    => $currency?->id ?? 0,
            'organization_id' => $organization?->id ?? 0,
            'group_id'        => $group?->id ?? 0,
            'company_id'      => $company?->id ?? 0
        ];
    }

    public static function getItemCostPrice($itemId, $attributes = [], $uomId, $currencyId, $transactionDate, $vendorId = null, $itemQty = 0)
    {
        return self::getItemPriceBase(
            $itemId,
            $attributes,
            $uomId,
            $currencyId,
            $transactionDate,
            $vendorId,
            $itemQty,
            'vendor'
        );
    }

    public static function getItemSalePrice($itemId, $attributes = [], $uomId, $currencyId, $transactionDate, $customerId = null, $itemQty = 0)
    {
        return self::getItemPriceBase(
            $itemId,
            $attributes,
            $uomId,
            $currencyId,
            $transactionDate,
            $customerId,
            $itemQty,
            'customer'
        );
    }

    private static function getItemPriceBase($itemId, $attributes, $uomId, $currencyId, $transactionDate, $partyId, $itemQty, $type = 'vendor')
    {
        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true);
        } elseif (is_object($attributes)) {
            $attributes = (array) $attributes;
        }

        $costPrice = 0;
        $costPriceCurrency = null;
        $uomConversion = 0;

        $orgDetails = self::getUserOrgDetails();
        $organizationId = $orgDetails['organization_id'];
        $groupId = $orgDetails['group_id'];
        $companyId = $orgDetails['company_id'];
        $orgCurrencyId = $orgDetails['org_currency_id'];

        $item = Item::find($itemId);

        if ($partyId) {
            $rateContractQuery = ErpRateContract::where("{$type}_id", $partyId)
                ->whereJsonContains('applicable_organizations', (string)$organizationId)
                ->where(function ($q) {
                    $q->where('document_status', ConstantHelper::APPROVED)
                        ->orWhere('document_status', ConstantHelper::APPROVAL_NOT_REQUIRED);
                })
                ->where('start_date', '<=', $transactionDate)
                ->where(function ($q) use ($transactionDate) {
                    $q->where('end_date', '>=', $transactionDate)->orWhereNull('end_date');
                })
                ->withWhereHas('items', function ($query) use ($itemId, $itemQty, $transactionDate, $attributes, $uomId) {
                    $query->where('item_id', $itemId)
                        ->where('from_qty', '<=', $itemQty)
                        ->where(function ($q) use ($itemQty) {
                            $q->whereNull('to_qty')->orWhere('to_qty', '>=', $itemQty);
                        })
                        ->where('from_date', '<=', $transactionDate)
                        ->where(function ($q) use ($transactionDate) {
                            $q->whereNull('to_date')->orWhere('to_date', '>=', $transactionDate);
                        })
                        ->where('uom_id', $uomId)
                        ->where(function ($subQuery) use ($attributes) {
                            if (empty($attributes)) return;
                            foreach ($attributes as $attr) {
                                if (is_object($attr)) $attr = (array)$attr;
                                $subQuery->orWhereHas('item_attributes', function ($attrQuery) use ($attr) {
                                    $attrQuery->where('attr_name', $attr['attr_name'] ?? $attr['attribute_name'] ?? $attr['group_name'])
                                        ->where('attr_value', $attr['attr_value'] ?? $attr['attribute_value']);
                                });
                            }
                        });
                });

            $rateContract = $rateContractQuery->first();
            if ($rateContract) {
                $costPrice = floatval($rateContract->items[0]->rate);
            }

            if (!$costPrice) {
                $rateContractQuery = ErpRateContract::where("{$type}_id", $partyId)
                    ->whereJsonContains('applicable_organizations', (string)$organizationId)
                    ->where(function ($q) {
                        $q->where('document_status', ConstantHelper::APPROVED)
                            ->orWhere('document_status', ConstantHelper::APPROVAL_NOT_REQUIRED);
                    })
                    ->where('start_date', '<=', $transactionDate)
                    ->where(function ($q) use ($transactionDate) {
                        $q->where('end_date', '>=', $transactionDate)->orWhereNull('end_date');
                    })
                    ->withWhereHas('items', function ($query) use ($itemId, $itemQty, $transactionDate, $attributes, $uomId) {
                        $query->where('item_id', $itemId)
                            ->where('from_qty', '<=', $itemQty)
                            ->where(function ($q) use ($itemQty) {
                                $q->whereNull('to_qty')->orWhere('to_qty', '>=', $itemQty);
                            })
                            ->where('from_date', '<=', $transactionDate)
                            ->where(function ($q) use ($transactionDate) {
                                $q->whereNull('to_date')->orWhere('to_date', '>=', $transactionDate);
                            })
                            ->where('uom_id', $uomId)
                            ->where(function ($subQuery) {
                                $subQuery->whereDoesntHave('item_attributes');
                            });
                    });

                $rateContract = $rateContractQuery->first();
                if ($rateContract) {
                    $costPrice = floatval($rateContract->items[0]->rate);
                }
            }

            if (!$costPrice) {
                $relation = $type === 'vendor' ? 'approvedVendors' : 'approvedCustomers';
                $priceField = $type === 'vendor' ? 'cost_price' : 'sell_price';
                $relationModel = $item->$relation
                    ->where("{$type}_id", $partyId)
                    ->where('uom_id', $uomId)
                    ->first();
                if ($relationModel) {
                    $costPrice = floatval($relationModel?->$priceField ?? 0);
                }
            }
            $party = ($type === 'vendor') ? Vendor::find($partyId) : Customer::find($partyId);
            $costPriceCurrency = $party?->currency_id ?? null;
        }

        if (!$costPrice) {
            $altUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $priceField = $type === 'vendor' ? 'cost_price' : 'sell_price';
            if ($altUom) {
                $uomConversion = $altUom->conversion_to_inventory;
                if (isset($altUom->$priceField) && $altUom->$priceField) {
                    $costPrice = floatval($altUom->$priceField);
                }
            }
            $costPriceCurrency = ($type == 'vendor' ? $item?->cost_price_currency_id : $item?->sell_price_currency_id);
        }
        if (!$costPrice) {
            $priceField = $type === 'vendor' ? 'cost_price' : 'sell_price';
            if ($uomId == $item->uom_id) {
                $costPrice = floatval($item?->$priceField);
            } elseif ($uomConversion) {
                $costPrice = floatval($item?->$priceField * $uomConversion);
            }
            $costPriceCurrency = ($type == 'vendor' ? $item?->cost_price_currency_id : $item?->sell_price_currency_id);
        }

        if (!$costPriceCurrency) {
            $costPriceCurrency = $orgCurrencyId;
        }

        $exchangeRate = 1;
        if ($costPriceCurrency != $currencyId) {
            $exchangeRate = CurrencyHelper::getCurrencyExchangeRate($costPriceCurrency, $currencyId, $transactionDate, $groupId, $companyId, $organizationId);
            if ($exchangeRate) {
                $costPrice = floatval($costPrice * $exchangeRate);
            } else {
                $costPrice = 0;
            }
        }
        
        return round($costPrice, 4);
    }

    public static function convertToBaseUom(int $itemId, int $altUomId, float $altQty) : float
    {
        $baseUomQty = 0;
        $item = Item::find($itemId);
        if (isset($item)) {
            $baseUomId = $item -> uom_id;
            //Same UOM
            if ($altUomId === $baseUomId) {
                $baseUomQty = $altQty;
            } else {
                $conversion = AlternateUOM::where('item_id', $itemId) -> where('uom_id', $altUomId) -> first();
                if (isset($conversion)) {
                    $baseUomQty = round($altQty * $conversion -> conversion_to_inventory, 2);
                }
            }
        }
        return $baseUomQty;
    }

    public static function convertToAltUom(int $itemId, int $altUomId, float $baseQty) : float
    {
        $altUomQty = 0;
        $item = Item::find($itemId);
        if (isset($item)) {
            $baseUomId = $item -> uom_id;
            //Same UOM
            if ($altUomId === $baseUomId) {
                $altUomQty = $baseQty;
            } else {
                $conversion = AlternateUOM::where('item_id', $itemId) -> where('uom_id', $altUomId) -> first();
                if (isset($conversion)) {
                    // $altUomQty = round($baseQty / $conversion -> conversion_to_inventory, 2);
                    $altUomQty = ($baseQty / $conversion -> conversion_to_inventory);
                }
            }
        }
        return $altUomQty;
    }

    public static function getItemApprovedVendors($itemId,$documentDate = null) 
    {
        // dd($itemId,$documentDate);
        // $vendorItems = VendorItem::withDefaultGroupCompanyOrg()
        //             ->where('item_id',$itemId)
        //             ->get();
        // $approvedVendorIds = [];
        // foreach($vendorItems as $vendorItem) {
        //     if(self::validateVendor($vendorItem->vendor_id,$documentDate)) {
        //         $approvedVendorIds[] = $vendorItem->vendor_id;
        //     }
        // }

        $approvedVendorIds = VendorItem::withDefaultGroupCompanyOrg()
                    ->where('item_id',$itemId)
                    ->pluck('vendor_id')
                    ->toArray();
        return $approvedVendorIds;
    }

    public static function validateVendor($vendorId, $documentDate = null)
    {
        $vendor = Vendor::find($vendorId);
        $currency = $vendor->currency;
        $paymentTerm = $vendor->paymentTerms;
        $shipping = $vendor->addresses()->where(function($query) {
                        $query->where('type', 'shipping')->orWhere('type', 'both');
                    })->latest()->first();
        $billing = $vendor->addresses()->where(function($query) {
                    $query->where('type', 'billing')->orWhere('type', 'both');
                })->latest()->first();

        $vendorId = $vendor->id;
        $billingAddresses = ErpAddress::where('addressable_id', $vendorId) -> where('addressable_type', Vendor::class) -> whereIn('type', ['billing', 'both'])-> get();
        $shippingAddresses = ErpAddress::where('addressable_id', $vendorId) -> where('addressable_type', Vendor::class) -> whereIn('type', ['shipping','both'])-> get();
        foreach ($billingAddresses as $billingAddress) {
            $billingAddress -> value = $billingAddress -> id;
            $billingAddress -> label = $billingAddress -> display_address;
        }
        foreach ($shippingAddresses as $shippingAddress) {
            $shippingAddress -> value = $shippingAddress -> id;
            $shippingAddress -> label = $shippingAddress -> display_address;
        }
        if (count($shippingAddresses) == 0) {
            return false;
        }
        if (count($billingAddresses) == 0) {
            return false;
        }
        if (!isset($vendor->currency_id)) {
            return false;
        }
        if (!isset($vendor->payment_terms_id)) {
            return false;
        }
        $documentDate = $documentDate ?? date('Y-m-d');
        $currencyData = CurrencyHelper::getCurrencyExchangeRates($vendor->currency_id ?? 0, $documentDate ?? '');
        if(!$currencyData['status']) {
            return false;
        }
        return true;
    }

    public static function getCustomerItemDetails(int $itemId, int $customerId) : array
    {
        $approvedCustomer = CustomerItem::withDefaultGroupCompanyOrg()
            -> where('item_id', $itemId) -> where('customer_id', $customerId) -> first();
        return array(
            'customer_item_id' => $approvedCustomer ?-> id,
            'customer_item_code' => $approvedCustomer ?-> item_code,
            'customer_item_name' => $approvedCustomer ?-> item_name,
        );
    }

    public static function getBomSafetyBufferPerc(int $bomId) : float
    {
        $bom = Bom::where('id', $bomId)->first();
        $safetyBuffer = 0;
        if(!$bom) return $safetyBuffer;
        if(isset($bom->safety_buffer_perc) && $bom->safety_buffer_perc) {
            $safetyBuffer = $bom->safety_buffer_perc;
        } else {
            $safetyBuffer = $bom?->productionRoute?->safety_buffer_perc ?? 0;
        }
        return $safetyBuffer;
    }

}