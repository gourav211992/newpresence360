<?php

namespace App\Helpers;

use stdClass;
use App\Models\Bom;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\Address;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\ErpStore;
use App\Models\BomDetail;
use App\Models\ErpAddress;
use App\Models\VendorItem;
use App\Models\ErpSubStore;
use App\Models\AlternateUOM;
use App\Models\CustomerItem;
use App\Models\Organization;
use App\Helpers\CurrencyHelper;
use App\Models\ErpRateContract;
use App\Models\OrganizationGroup;
use Illuminate\Support\Collection;
use App\Models\OrganizationCompany;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use App\Helpers\SubStore\Constants as SubStoreConstants;
use App\Models\ErpAttribute;

class ItemHelper
{
    /**
     * Get item attributes with their group name and value.
     *
     * @param array $attributes  Input array containing 'attribute_value' IDs.
     * @return array             List of ['attribute_name', 'attribute_value'].
     */
    public static function getItemAttributesWithValues(array $attributes)
    {
        $attributeDetails = [];

        foreach ($attributes as $attribute) {
            // Fetch ERP attribute with its group by ID
            $erpAttribute = ErpAttribute::with('attributeGroup')
                ->where('id', $attribute['attribute_value'])
                ->select('id', 'value', 'attribute_group_id')
                ->first();

            if (!$erpAttribute) continue; // Skip if not found

            // Store group name and attribute value
            $attributeDetails[] = [
                'attribute_name'  => $erpAttribute->attributeGroup?->name,
                'attribute_value' => $erpAttribute->value
            ];
        }

        return $attributeDetails;
    }

    /* array : $itemAttributes should be in the form -> [['attribute_id' => 1, 'attribute_value' => 10]] */
    public static function checkItemBomExists(int $itemId, array $itemAttributes, $bomType = 'bom', $customerId = null): array|null
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
        //Traded Item Sub Type
        $tradedItem = $item->is_traded_item;
        if (in_array($subType, ['Finished Goods', 'WIP/Semi Finished'])) {
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
        $itemBoms = Bom::withDefaultGroupCompanyOrg()->where('bom_type', ConstantHelper::FIXED)->where('item_id', $item->id)
            ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
            ->where(function ($query) use ($bomType, $customerId) {
                if ($bomType == ConstantHelper::BOM_SERVICE_ALIAS) {
                    $query->where('type', $bomType);
                } else {
                    if ($customerId) {
                        $query->where('customer_id', $customerId);
                    }
                }
            })
            ->get();
        if (!isset($itemBoms) || count($itemBoms) == 0) {
            //If Traded Item, then ignore
            if ($tradedItem) {
                return array(
                    'status' => 'bom_not_required',
                    'bom_id' => null,
                    'message' => 'BOM not required',
                    'sub_type' => $subType,
                    'customizable' => null
                );
            } else {
                return array(
                    'status' => 'bom_not_exists',
                    'bom_id' => null,
                    'message' => 'BOM does not exist',
                    'sub_type' => $subType,
                    'customizable' => null
                );
            }
        }
        $matchedBomId = $itemBoms[0]->id ?? null;
        //Check if all atributes are selected
        $actualItemAttributes = $item->itemAttributes;
        $attributes = array();
        foreach ($actualItemAttributes as $currentAttribute) {
            if ($currentAttribute?->required_bom) {
                array_push($attributes, $currentAttribute);
            }
        }
        //Compare all BOM with required BOM attribute values
        if (count($attributes) > 0) {
            $matchedBomId = null;
            foreach ($itemBoms as $bom) {
                $attributeBomCreated = false;
                foreach ($bom->bomAttributes as $attribute) {
                    $reqBomAttribute = array_filter($attributes, function ($reqAttribute) use ($attribute) {
                        return $reqAttribute->id == $attribute->item_attribute_id;
                    });
                    if ($reqBomAttribute && count($reqBomAttribute) > 0) {
                        $matchingAttribute = array_filter($itemAttributes, function ($itemAttribute) use ($attribute) {
                            return $itemAttribute['attribute_value'] == $attribute->attribute_value && $itemAttribute['attribute_id'] == $attribute->item_attribute_id;
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
                    $matchedBomId = $bom->id;
                    break;
                }
            }
        }
        $matchedBom = $matchedBomId ? Bom::find($matchedBomId) : null;
        return array(
            'status' => $matchedBomId ? 'bom_exists' : ($tradedItem ? 'bom_not_required' : 'bom_not_exists'),
            'bom_id' => $matchedBomId,
            'message' => $matchedBomId ? 'Bom exist' : ($tradedItem ? 'bom_not_required' : 'BOM does not exist'),
            'sub_type' => $subType,
            'customizable' => $matchedBom ? $matchedBom->customizable : null
        );
    }

    /*Created helper for the get created bom cost*/
    public static function getChildBomItemCost($itemId, $selectedAttributes = [])
    {
        $bomExist = self::checkItemBomExists($itemId, []);
        if (!$bomExist['bom_id']) {
            return ['cost' => 0, 'status' => 422, 'message' => 'Not found header in BOM'];
        }
        $bom = Bom::where('id', $bomExist['bom_id'])->first();
        if ($bom) {
            $totalValue = $bom->total_value ?? 0;
            return ['cost' => $totalValue, 'route' => route('bill.of.material.edit', $bom->id), 'status' => 200, 'message' => 'Fetched BOM header item cost'];
        }
    }

    public static function getBomIdNumbersOnItem($itemId)
    {
        $bom = null;
        $bomExists = self::checkItemBomExists($itemId, []);
        if ($bomExists && $bomExists['status'] == 'bom_exists')
            $bom = self::getItemBomIfExists($itemId, $bomExists['bom_id']);

        return $bom;
    }

    public static function getRecursiveBomIdNumbersOnItem($itemId, &$result = [], &$visited = [])
    {
        if (in_array($itemId, $visited, true)) return $result;
        $visited[] = $itemId;
        $bomExists = self::checkItemBomExists($itemId, []);
        if (!($bomExists && $bomExists['status'] == 'bom_exists'))  return $result;
        $bom = self::getItemBomIfExists($itemId, $bomExists['bom_id']);
        if (!$bom) return $result;
        $result[$bom->id] = $bom->document_number;
        foreach ($bom->bomItems as $bomItem) self::getRecursiveBomIdNumbersOnItem($bomItem->item_id, $result, $visited);

        return $result;
    }

    // public static function printChildBomRecursive($itemId, $level = 1, &$rowNumber = '')
    // {
    //     $bomExists = self::checkItemBomExists($itemId, []);
    //     if (!($bomExists && $bomExists['status'] == 'bom_exists')) {
    //         return;
    //     }

    //     $childBom = self::getItemBomIfExists($itemId, $bomExists['bom_id']);
    //     if (!$childBom || !$childBom->bomItems->count()) {
    //         return;
    //     }

    //     foreach ($childBom->bomItems as $childIndex => $childItem) {
    //         $childRowNumber = $rowNumber . '.' . ($childIndex + 1);

    //         echo view('pdf.bom_recursive', [
    //             'bomItem' => $childItem,
    //             'rowNumber' => $childRowNumber,
    //             'level' => $level
    //         ])->render();

    //         self::printChildBomRecursive($childItem->item_id, $level + 1, $childRowNumber);
    //     }
    // }

    public static function getItemBomIfExists($itemId, $bomId = null)
    {
        $bom = Bom::when($bomId, fn($q) => $q->where('id', $bomId))->where('item_id', $itemId)->with('bomItems')->first();
        return $bom;
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
            $currentSubType->name = $itemSubType?->subType?->name;
            $actualItemSubTypes->push($currentSubType);
        }

        $subType = collect($actualItemSubTypes)->whereIn('name', ['Finished Goods'])->first();
        if ($subType) {
            $name = $subType?->name;
        }

        if (!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name', ['WIP/Semi Finished'])->first();
            if ($subType) {
                $name = $subType?->name;
            }
        }

        if (!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name', ['Raw Material'])->first();
            if ($subType) {
                $name = $subType?->name;
            }
        }

        if (!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name', ['Asset'])->first();
            if ($subType) {
                $name = $subType?->name;
            }
        }

        if (!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name', ['Expense'])->first();
            if ($subType) {
                $name = $subType?->name;
            }
        }

        if (!$name) {
            $subType = collect($actualItemSubTypes)->whereIn('name', ['Traded Item'])->first();
            if ($subType) {
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
        if ($item?->alternateUOMs->count()) {
            if ($uomType == 'purchase') {
                $altUom = $item->alternateUOMs()->where('is_purchasing', 1)->first();
                $altUom = $altUom->id ?? null;
            }
            if ($uomType == 'selling') {
                $altUom = $item->alternateUOMs()->where('is_selling', 1)->first();
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
        $itemQty = $itemQty ?? 0;

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

        if (!$currencyId) {
            $currencyId = $orgCurrencyId;
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

    public static function convertToBaseUom(int $itemId, int $altUomId, float $altQty): float
    {
        $baseUomQty = 0;
        $item = Item::find($itemId);
        if (isset($item)) {
            $baseUomId = $item->uom_id;
            //Same UOM
            if ($altUomId === $baseUomId) {
                $baseUomQty = $altQty;
            } else {
                $conversion = AlternateUOM::where('item_id', $itemId)->where('uom_id', $altUomId)->first();
                if (isset($conversion)) {
                    $baseUomQty = round($altQty * $conversion->conversion_to_inventory, 2);
                }
            }
        }
        return $baseUomQty;
    }

    public static function convertToAltUom(int $itemId, int $altUomId, float $baseQty): float
    {
        $altUomQty = 0;
        $item = Item::find($itemId);
        if (isset($item)) {
            $baseUomId = $item->uom_id;
            //Same UOM
            if ($altUomId === $baseUomId) {
                $altUomQty = $baseQty;
            } else {
                $conversion = AlternateUOM::where('item_id', $itemId)->where('uom_id', $altUomId)->first();
                if (isset($conversion)) {
                    // $altUomQty = round($baseQty / $conversion -> conversion_to_inventory, 2);
                    $altUomQty = ($baseQty / $conversion->conversion_to_inventory);
                }
            }
        }
        return $altUomQty;
    }

    public static function getItemApprovedVendors($itemId, $documentDate = null)
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
            ->where('item_id', $itemId)
            ->pluck('vendor_id')
            ->toArray();
        return $approvedVendorIds;
    }

    public static function validateVendor($vendorId, $documentDate = null)
    {
        $vendor = Vendor::find($vendorId);
        $currency = $vendor->currency;
        $paymentTerm = $vendor->paymentTerms;
        $shipping = $vendor->addresses()->where(function ($query) {
            $query->where('type', 'shipping')->orWhere('type', 'both');
        })->latest()->first();
        $billing = $vendor->addresses()->where(function ($query) {
            $query->where('type', 'billing')->orWhere('type', 'both');
        })->latest()->first();

        $vendorId = $vendor->id;
        $billingAddresses = ErpAddress::where('addressable_id', $vendorId)->where('addressable_type', Vendor::class)->whereIn('type', ['billing', 'both'])->get();
        $shippingAddresses = ErpAddress::where('addressable_id', $vendorId)->where('addressable_type', Vendor::class)->whereIn('type', ['shipping', 'both'])->get();
        foreach ($billingAddresses as $billingAddress) {
            $billingAddress->value = $billingAddress->id;
            $billingAddress->label = $billingAddress->display_address;
        }
        foreach ($shippingAddresses as $shippingAddress) {
            $shippingAddress->value = $shippingAddress->id;
            $shippingAddress->label = $shippingAddress->display_address;
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
        if (!$currencyData['status']) {
            return false;
        }
        return true;
    }

    public static function getCustomerItemDetails(int $itemId, int $customerId): array
    {
        $approvedCustomer = CustomerItem::withDefaultGroupCompanyOrg()
            ->where('item_id', $itemId)->where('customer_id', $customerId)->first();
        return array(
            'customer_item_id' => $approvedCustomer?->id,
            'customer_item_code' => $approvedCustomer?->item_code,
            'customer_item_name' => $approvedCustomer?->item_name,
        );
    }

    public static function getBomSafetyBufferPerc(int $bomId): float
    {
        $bom = Bom::where('id', $bomId)->first();
        $safetyBuffer = 0;
        if (!$bom) return $safetyBuffer;
        if (isset($bom->safety_buffer_perc) && $bom->safety_buffer_perc) {
            $safetyBuffer = $bom->safety_buffer_perc;
        } else {
            $safetyBuffer = $bom?->productionRoute?->safety_buffer_perc ?? 0;
        }
        return $safetyBuffer;
    }

    private static function processItemAndBOM(Collection &$processedItems, Item $item, array $itemAttributes, int $uomId, $organizationId = null, $locationId = null, $subStoreId = null, $filterItemIds = [])
    {
        $selectedAttributes = [];
        $attributesUI = "";

        foreach ($itemAttributes as $itemAttr) {
            $selectedAttributes[] = $itemAttr['attribute_value_id'];
            $attributesUI .= "<span class='badge rounded-pill badge-light-primary'>$itemAttr[attribute_name] : $itemAttr[attribute_value]</span>";
        }

        //Repeatedly add Stock to every Organization, Location and Store
        $authUser = Helper::getAuthenticatedUser();
        // dd($authUser);
        $orgIds = [];
        if ($organizationId) {
            $orgIds[] = $organizationId;
        } else {
            $orgIds = $authUser->organizations?->pluck('id')->toArray();
            array_push($orgIds, $authUser->organization_id);
        }
        //Retrieve all Organizations
        $orgs = Organization::select('id', 'name')->whereIn('id', $orgIds)->get();
        $orgIds = $orgs->pluck('id')->toArray();
        //Retrieve all locations
        $locations = [];
        $locationIds = [];
        if ($locationId) {
            $allLocations = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->where('id', $locationId)->withWhereHas('subStores', function ($subStoreQuery) {
                $subStoreQuery->whereHas('sub_type', function ($subTypeQuery) {
                    $subTypeQuery->where('type', SubStoreConstants::MAIN_STORE_VALUE);
                });
            })->where('status', ConstantHelper::ACTIVE)->get();
        } else {
            $allLocations = ErpStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->whereIn('organization_id', $orgIds)
                ->when(($authUser->authenticable_type == "employee"), function ($locationQuery) use ($authUser) { // Location with same country and state
                    $locationQuery->whereHas('employees', function ($employeeQuery) use ($authUser) {
                        $employeeQuery->where('employee_id', $authUser->id);
                    });
                })->withWhereHas('subStores', function ($subStoreQuery) {
                    $subStoreQuery->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->whereHas('sub_type', function ($subTypeQuery) {
                        $subTypeQuery->where('type', SubStoreConstants::MAIN_STORE_VALUE);
                    });
                })->where('status', ConstantHelper::ACTIVE)->get();
        }
        $locationIds = $allLocations->pluck('id')->toArray();
        foreach ($orgs as $org) {
            $locations = $allLocations->where('organization_id', $org->id);
            foreach ($locations as $location) {
                $subStores = [];
                if ($subStoreId) {
                    $subStores = ErpSubStore::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->where('id', $subStoreId)->get();
                } else {
                    $subStores = $location->subStores;
                }
                foreach ($subStores as $subStore) {
                    if (count($filterItemIds) > 0) {
                        if (!in_array($item->id, $filterItemIds)) {
                            continue;
                        }
                    }
                    $totalStocks = InventoryHelper::totalInventoryAndStock($item->id, $selectedAttributes, $uomId, $location->id, $subStore?->id ?? 0);
                    $confirmedStocks = $totalStocks['confirmedStocks'] ?? 0.00;
                    $unconfirmedStocks = $totalStocks['pendingStocks'] ?? 0.00;

                    $processedItems->push([
                        'item_id' => $item->id,
                        'item_code' => $item->item_code,
                        'item_name' => $item->item_name,
                        'group_id' => $item->group_id,
                        'company_id' => $item->company_id,
                        'company_name' => $item->company?->name,
                        'organization_id' => $org->id,
                        'organization_name' => $org?->name,
                        'uom_id' => $item->uom_id,
                        'uom_name' => $item->uom?->alias,
                        'attributes_ui' => $attributesUI,
                        'location_id' => $location->id,
                        'location_name' => $location->store_name,
                        'sub_store_id' => $subStore?->id,
                        'sub_store_name' => $subStore?->name,
                        'confirmed_stocks' => number_format($confirmedStocks, 2),
                        'unconfirmed_stocks' => number_format($unconfirmedStocks, 2),
                        'selected_attributes' => $selectedAttributes
                    ]);
                }
            }
        }

        // Recursively handle BOM items
        $itemBom = ItemHelper::checkItemBomExists($item->id, []);
        if (!isset($itemBom['bom_id'])) return;

        $bomItems = BomDetail::where('bom_id', $itemBom['bom_id'])->get();

        foreach ($bomItems as $bomItem) {
            $bomAttributes = [];
            $itemAttributesForChild = [];

            foreach ($bomItem->attributes as $bomAttr) {
                $attributeName = $bomAttr->headerAttribute?->name;
                $attributeValue = $bomAttr->headerAttributeValue?->value;
                $bomAttributes[] = $bomAttr->attribute_value;

                $itemAttributesForChild[] = [
                    'attribute_value_id' => $bomAttr->attribute_value,
                    'attribute_name' => $attributeName,
                    'attribute_value' => $attributeValue,
                ];
            }

            self::processItemAndBOM($processedItems, $bomItem->item, $itemAttributesForChild, $uomId, $organizationId, $locationId, $subStoreId, $filterItemIds);
        }
    }


    public static function generateOrgLocStoreWiseItemStock(Item $item, array $itemAttributes, int $uomId, $orgId, $locId, $subStoreId, $filterItemIds = [])
    {

        $processedItems = new Collection();
        self::processItemAndBOM($processedItems, $item, $itemAttributes, $uomId, $orgId, $locId, $subStoreId, $filterItemIds);
        return $processedItems;
    }

    public static function checkBomForItem(int $itemId): array
    {
        $bomHeader = Bom::withDefaultGroupCompanyOrg()
            ->where('item_id', $itemId)
            ->whereIn('document_status', [
                ConstantHelper::APPROVED,
                ConstantHelper::APPROVAL_NOT_REQUIRED
            ])
            ->first();

        if ($bomHeader) {
            return [
                'status' => 'bom_exists',
                'bom_id' => $bomHeader->id,
                'message' => 'BOM exists for this item in BOM header',
            ];
        }

        $bomDetail = BomDetail::where('item_id', $itemId)
            ->withWhereHas('bom', function ($query) {
                $query->withDefaultGroupCompanyOrg()
                    ->whereIn('document_status', [
                        ConstantHelper::APPROVED,
                        ConstantHelper::APPROVAL_NOT_REQUIRED
                    ]);
            })
            ->first();

        if ($bomDetail && $bomDetail->bom) {
            return [
                'status' => 'bom_exists',
                'bom_id' => $bomDetail->bom->id,
                'message' => 'BOM exists for this item in BOM detail',
            ];
        }

        return [
            'status' => 'bom_not_exists',
            'bom_id' => null,
            'message' => 'BOM does not exist for this item',
        ];
    }

    public static function setBomPrintTable($id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();

        $canView = true;
        $bom = Bom::findOrFail($id);
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        if ($servicesAliasParam == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            $canView = $user?->hasPermission('quotation_bom.item_cost_view') ?? true;
        }
        if ($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            $canView = $user?->hasPermission('production_bom.item_cost_view') ?? true;
        }

        $title = 'Production Bom';
        if ($bom->type != ConstantHelper::BOM_SERVICE_ALIAS) {
            $title = 'Quotation Bom';
        }

        $specifications = collect();
        if (isset($bom->item) && $bom->item) {
            $specifications = $bom->item->specifications()->whereNotNull('value')->get();
        }

        $totalAmount = $bom->total_value;
        $amountInWords = NumberHelper::convertAmountToWords($totalAmount);
        $imagePath = public_path('assets/css/midc-logo.jpg');
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$bom->document_status] ?? '';

        $response = BookHelper::fetchBookDocNoAndParameters($bom->book_id, $bom->document_date);
        $parameters = json_decode(json_encode($response['data']['parameters']), true) ?? [];
        $sectionRequired = isset($parameters['section_required']) && is_array($parameters['section_required']) && in_array('yes', array_map('strtolower', $parameters['section_required']));
        $subSectionRequired = isset($parameters['sub_section_required']) && is_array($parameters['sub_section_required']) && in_array('yes', array_map('strtolower', $parameters['sub_section_required']));
        $consumption_method = isset($parameters['consumption_method']) && $parameters['consumption_method'][0] == 'manual' ? false : true;

        echo view('pdf.bom_item_details', [
            'bom' => $bom,
            'user' => $user,
            'title' => $title,
            'canView' => $canView,
            'imagePath' => $imagePath,
            'totalAmount' => $totalAmount,
            'organization' => $organization,
            'amountInWords' => $amountInWords,
            'specifications' => $specifications,
            'docStatusClass' => $docStatusClass,
            'sectionRequired' => $sectionRequired,
            'consumption_method' => $consumption_method,
            'subSectionRequired' => $subSectionRequired,
            'organizationAddress' => $organizationAddress,

        ])->render();
    }

    // public static function generateOrgLocStoreWiseItemStock(Item $item, array $itemAttributes, int $uomId, ErpStore $location)
    // {
    //     $processedItems = new Collection();
    //     $itemBom = ItemHelper::checkItemBomExists($item -> id, []);
    //     //Get Sub stores
    //     $subStores = InventoryHelper::getAccesibleSubLocations($location -> id);
    //     $firstSubStore = $subStores -> first();
    //     //Add the Main Item First
    //     $selectedAttributes = [];
    //     $attributesUI = "";
    //     foreach ($itemAttributes as $itemAttr) {
    //         array_push($selectedAttributes, $itemAttr['attribute_value_id']);
    //         //Make attributes UI
    //         $attributeName = $itemAttr['attribute_name'];
    //         $attributeValue = $itemAttr['attribute_value'];
    //         $attributesUI .= "<span class='badge rounded-pill badge-light-primary' > $attributeName : $attributeValue </span>";
    //     }
    //     $totalStocks = InventoryHelper::totalInventoryAndStock($item -> id, $selectedAttributes, $uomId, $location -> id, $firstSubStore ?-> id ?? 0);
    //     $confirmedStocks = isset($totalStocks['confirmedStocks']) ? $totalStocks['confirmedStocks'] : 0.00;
    //     $unconfirmedStocks = isset($totalStocks['pendingStocks']) ? $totalStocks['pendingStocks'] : 0.00;
    //     $processedItems -> push([
    //         'item_id' => $item -> id,
    //         'item_code' => $item -> item_code,
    //         'item_name' => $item -> item_name,
    //         'group_id' => $item -> group_id,
    //         'company_id' => $item -> company_id,
    //         'company_name' => $item -> company ?-> name,
    //         'organization_id' => $item -> organization_id,
    //         'organization_name' => $item -> organization ?-> name,
    //         'uom_id' => $item -> uom_id,
    //         'uom_name' => $item -> uom ?-> alias,
    //         'attributes_ui' => $attributesUI,
    //         'location_id' => $location -> id,
    //         'location_name' => $location -> store_name,
    //         'sub_store_id' => $firstSubStore ?-> id,
    //         'sub_store_name' => $firstSubStore ?-> name,
    //         'confirmed_stocks' => number_format($confirmedStocks, 2),
    //         'unconfirmed_stocks' => number_format($unconfirmedStocks, 2),
    //         'selected_attributes' => $selectedAttributes
    //     ]);
    //     //Add BOM items if exists
    //     if (isset($itemBom['bom_id'])) {
    //         $bomItems = BomDetail::where('bom_id', $itemBom['bom_id']) -> get();
    //         foreach ($bomItems as $bomItem) {
    //             $bomItemAttributes = [];
    //             $bomAttributesUI = "";
    //             foreach ($bomItem -> attributes as $bomAttr) {
    //                 array_push($bomItemAttributes, $bomAttr -> attribute_value);
    //                 $attributeName = $bomAttr -> headerAttribute ?-> name;
    //                 $attributeValue = $bomAttr -> headerAttributeValue ?-> value;
    //                 $bomAttributesUI .= "<span class='badge rounded-pill badge-light-primary' > $attributeName : $attributeValue </span>";
    //             }
    //             $totalStocks = InventoryHelper::totalInventoryAndStock($bomItem -> item_id, $bomItemAttributes, $uomId, $location -> id, $firstSubStore ?-> id ?? 0);
    //             $confirmedStocks = isset($totalStocks['confirmedStocks']) ? $totalStocks['confirmedStocks'] : 0.00;
    //             $unconfirmedStocks = isset($totalStocks['pendingStocks']) ? $totalStocks['pendingStocks'] : 0.00;
    //             $processedItems -> push([
    //                 'item_id' => $bomItem -> item_id,
    //                 'item_code' => $bomItem -> item_code,
    //                 'item_name' => $bomItem ?-> item ?-> item_name,
    //                 'group_id' => $item -> group_id,
    //                 'company_id' => $item -> company_id,
    //                 'company_name' => $item -> company ?-> name,
    //                 'organization_id' => $item -> organization_id,
    //                 'organization_name' => $item -> organization ?-> name,
    //                 'uom_id' => $bomItem ?-> item -> uom_id,
    //                 'uom_name' => $bomItem ?-> item -> uom ?-> alias,
    //                 'attributes_ui' => $bomAttributesUI,
    //                 'location_id' => $location -> id,
    //                 'location_name' => $location -> store_name,
    //                 'sub_store_id' => $firstSubStore ?-> id,
    //                 'sub_store_name' => $firstSubStore ?-> name,
    //                 'confirmed_stocks' => number_format($confirmedStocks, 2),
    //                 'unconfirmed_stocks' => number_format($unconfirmedStocks, 2),
    //                 'selected_attributes' => $bomItemAttributes
    //             ]);
    //         }
    //     }
    //     return $processedItems;
    // }
}
