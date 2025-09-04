<?php

namespace App\Imports;

use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Helpers\SaleModuleHelper;
use App\Models\Attribute;
use App\Models\Book;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\SubType;
use App\Models\Unit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use stdClass;

class PurchaseOrderImport implements ToArray, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    private $bookId = null;
    private $locationId = null;
    private $authUserId = null;
    private $procurement_type = null;

    public function __construct(int $bookId, int $locationId, int $authUserId,string $procurementType)
    {
        $this->bookId = $bookId;
        $this->locationId = $locationId;
        $this->authUserId = $authUserId;
        $this->procurement_type = $procurementType;
    }

    public function array(array $rows)
    {
        //Book and Location Validation
        $book = Book::find($this->bookId);
        $location = ErpStore::find($this->locationId);

        $companyCountryId = null;
        $companyStateId = null;
        $locationAddress = $location?->address;
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex) {
                $orderDetail = new stdClass();
                $errors = [];

                // Book and Location validation
                if (!isset($book)) {
                    $errors[] = 'Invalid Book';
                }
                if ($location && isset($locationAddress)) {
                    $companyCountryId = $location->address?->country_id ?? null;
                    $companyStateId = $location->address?->state_id ?? null;
                } else {
                    $errors[] = 'Invalid Location or location address not available';
                }

                // Order No
                $orderDetail->order_no = $row['order_no'];
                if (!$orderDetail->order_no) {
                    $errors[] = "Document Number not specified";
                }
                //Procurement Type
                // if ($row['procurement_type']) {
                //     if(strtolower($row['procurement_type']) == 'buy' || strtolower($row['procurement_type']) == 'lease'){
                //         $orderDetail->procurement_type = strtolower($row['procurement_type']);
                //     } else {
                //         $errors[] = "Invalid Procurement Type specified";
                //     }
                // }
                $orderDetail->procurement_type = $this->procurement_type;
                // Order Date
                if ($row['order_date']) {
                    $orderDetail->document_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['order_date'])->format('Y-m-d');
                    if (!$orderDetail->document_date) {
                        $orderDetail->document_date = Carbon::today()->format("Y-m-d");
                    }
                } else {
                    $orderDetail->document_date = Carbon::today()->format("Y-m-d");
                }

                // Document Number Validation
                $numberPatternData = Helper::generateDocumentNumberNew($book->id, $orderDetail->document_date);
                if (!isset($numberPatternData)) {
                    $errors[] = 'Number Pattern for Series not specified';
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $orderDetail->order_no;

                $regeneratedDocExist = PurchaseOrder::withDefaultGroupCompanyOrg()
                    ->where('book_id', $book->id)
                    ->where('document_number', $document_number)
                    ->first();
                if (isset($regeneratedDocExist)) {
                    $errors[] = "Duplicate Document Number";
                }

                $bookParams = BookHelper::fetchBookDocNoAndParameters($this->bookId, $orderDetail->document_date);
                $parameters = $bookParams['data']['parameters'];

                if (isset($parameters->future_date_allowed[0]) && $parameters->future_date_allowed[0] == 'no') {
                    if (Carbon::parse($orderDetail->document_date)->gt(Carbon::today())) {
                        $errors[] = "Future Order Date is not allowed";
                    }
                }
                if (isset($parameters->back_date_allowed[0]) && $parameters->back_date_allowed[0] == "no") {
                    if (Carbon::parse($orderDetail->document_date)->lt(Carbon::today())) {
                        $errors[] = "Past Order Date is not allowed";
                    }
                }

                // Vendor Validation
                $orderDetail->vendor_code = $row['vendor_code'];
                if (!$orderDetail->vendor_code) {
                    $errors[] = "Vendor not specified";
                }

                $vendorSearch = strtolower(trim($orderDetail->vendor_code));

                $vendor = Vendor::withDefaultGroupCompanyOrg()
                    ->where('status', ConstantHelper::ACTIVE)
                    ->where(function ($query) use ($vendorSearch) {
                        $query->whereRaw('LOWER(company_name) = ?', [$vendorSearch])
                              ->orWhereRaw('LOWER(vendor_code) = ?', [$vendorSearch]);
                    })
                    ->first();

                if (!isset($vendor)) {
                    $errors[] = "Vendor not found or is inactive";
                } else {
                    $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($vendor->currency_id, $orderDetail->document_date);
                    if ($currencyExchangeData['status'] == false) {
                        $errors[] = $currencyExchangeData['message'];
                    }

                    $vendorAddresses = $vendor->addresses;
                    $vendorBillAddress = $vendorAddresses->whereIn('type', ['billing', 'both'])->first();
                    if (!isset($vendorBillAddress)) {
                        $errors[] = 'Vendor Billing Address not found';
                    }
                    // $vendorShipAddress = $vendorAddresses->whereIn('type', ['shipping', 'both'])->first();
                    // if (!isset($vendorShipAddress)) {
                    //     $errors[] = 'Vendor Shipping Address not found';
                    // }
                }
                $orderDetail->vendor_id = $vendor?->id;

                // Consignee Name
                $orderDetail->consignee_name = $row['consignee_name'] ?? null;

                // Item
                $totalQty = 0;
                $attributesArray = [];
                $orderDetail->item_code = $row['item_code'];
                if (!$orderDetail->item_code) {
                    $errors[] = "Item not specified";
                }

                
                $itemSearch = strtolower(trim($orderDetail->item_code));
                
                $subTypeIds = SubType::whereIn('name', [
                    ConstantHelper::FINISHED_GOODS,
                    ConstantHelper::TRADED_ITEM,
                    ConstantHelper::ASSET,
                    ConstantHelper::WIP_SEMI_FINISHED
                    ])->get()->pluck('id')->toArray();
                    
                    $item = Item::withDefaultGroupCompanyOrg()
                    ->where('status', ConstantHelper::ACTIVE)
                    ->whereHas('subTypes', function ($subTypeQuery) use ($subTypeIds) {
                        $subTypeQuery->whereIn('sub_type_id', $subTypeIds);
                    })
                    ->where('type', ConstantHelper::GOODS)
                    ->where(function ($query) use ($itemSearch) {
                        $query->whereRaw('LOWER(item_code) = ?', [$itemSearch])
                        ->orWhereRaw('LOWER(item_name) = ?', [$itemSearch]);
                    })
                    ->first();
                    
                    $orderDetail->item_id = $item?->id;
                    
                if (!isset($item)) {
                    $errors[] = "Item not found or invalid item specified";
                } else {
                    // Attributes
                    $actualItemAttributes = $item->itemAttributes;
                    if ($actualItemAttributes && count($actualItemAttributes) > 0) {
                        $attributesString = $row['attributes'];
                        if (!$attributesString) {
                            $errors[] = "Item Attributes not specified";
                        }
                        $attributesArrayRaw = explode(',', $attributesString);
                        if (count($attributesArrayRaw) !== count($actualItemAttributes)) {
                            $errors[] = "All Attributes of item not specified";
                        }
                        $attributeNameValues = [];
                        foreach ($attributesArrayRaw as $attribute) {
                            $attributeKeyValue = explode(':', $attribute);
                            if (count($attributeKeyValue) == 2) {
                                $attributeNameValues[trim(strtolower($attributeKeyValue[1]))] = trim(strtolower($attributeKeyValue[0]));
                            }
                        }
                        foreach ($actualItemAttributes as $actualItemAttribute) {
                            $attribute = $actualItemAttribute->attributeGroup;
                            $attributeName = $attribute?->name;
                            $attributeNameLower = strtolower($attribute?->name);
                            $index = array_search($attributeNameLower, $attributeNameValues);
                            if ($index && $attributeNameLower == $attributeNameValues[$index]) {
                                $attributeValue = $index;
                                $attributeVal = Attribute::whereIn('id', $actualItemAttribute->attribute_id)
                                    ->whereRaw('LOWER(value) = ?', [$attributeValue])
                                    ->first();
                                if (isset($attributeVal)) {
                                    array_push($attributesArray, [
                                        'item_attribute_id' => $actualItemAttribute->id,
                                        'attribute_name' => $attribute?->id,
                                        'attr_name' => $attribute?->id,
                                        'attribute_value' => $attributeVal->id,
                                        'attr_value' => $attributeVal->id,
                                        'attribute_id' => $attributeVal->id,
                                    ]);
                                } else {
                                    $errors[] = "Invalid Item Attribute Value - $attributeValue specified for $attributeName";
                                    break;
                                }
                            } else {
                                $errors[] = "Invalid Item Attribute Name specified";
                                break;
                            }
                        }
                    }
                }

                $orderDetail->attributes = $attributesArray;
                // Qty
                $orderDetail->qty = floatval($row['qty']);
                if ($orderDetail->qty == 0) {
                    $errors[] = "Item Quantity not specified";
                } else if (floatval($orderDetail->qty) < 0) {
                    $errors[] = "Item Quantity cannot be negative";
                }

                // UOM
                $orderDetail->uom_code = $row['uom'];
                if ($orderDetail->uom_code) {
                    $itemUoms = [];
                    if ($item) {
                        array_push($itemUoms, $item->uom_id);
                        foreach ($item->alternateUOMs as $altUom) {
                            array_push($itemUoms, $altUom->uom_id);
                        }
                    }
                    $uomSearch = strtolower(trim($orderDetail->uom_code));
                    $uom = Unit::withDefaultGroupCompanyOrg()
                        ->where('status', ConstantHelper::ACTIVE)
                        ->whereRaw('LOWER(name) = ?', [$uomSearch])
                        ->when(count($itemUoms), function ($itemUomQuery) use ($itemUoms) {
                            $itemUomQuery->whereIn('id', $itemUoms);
                        })
                        ->first();
                    $orderDetail->uom_id = $uom?->id;
                    if (!$uom) {
                        $errors[] = "UOM not found";
                    }
                } else {
                    if ($item) {
                        $itemUom = $item->uom;
                        $itemSellingUom = null;
                        foreach ($item->alternateUOMs as $altUom) {
                            if ($altUom->is_selling) {
                                $itemSellingUom = $altUom->uom;
                            }
                        }
                        if (isset($itemSellingUom)) {
                            $orderDetail->uom_code = $itemSellingUom->alias;
                            $orderDetail->uom_id = $itemSellingUom->id;
                        } else if (isset($itemUom)) {
                            $orderDetail->uom_code = $itemUom->alias;
                            $orderDetail->uom_id = $itemUom->id;
                        } else {
                            $errors[] = "No UOM found for this item";
                        }
                    }
                }

                // Rate
                $orderDetail->rate = floatval($row['rate']);
                if ($item && !$orderDetail->rate) {
                    $itemRate = SaleModuleHelper::getItemSellingPrice($item, $orderDetail->uom_id);
                    $orderDetail->rate = $itemRate;
                    if (floatval($orderDetail->rate) == 0) {
                        $errors[] = "Item Rate not specified and not found from Item";
                    }
                } else {
                    if (floatval($orderDetail->rate) == 0) {
                        $errors[] = "Item Rate not specified and not found from Item";
                    } else if (floatval($orderDetail->rate) < 0) {
                        $errors[] = "Item Rate cannot be negative";
                    }
                }

                // Delivery Date
                if ($row['delivery_date']) {
                    $orderDetail->delivery_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['delivery_date'])->format('Y-m-d');
                    if (Carbon::parse($orderDetail->delivery_date)->lt(Carbon::today())) {
                        $errors[] = "Past Delivery Date is not allowed";
                    }
                } else {
                    if (Carbon::parse($orderDetail->document_date)->gte(Carbon::today())) {
                        $orderDetail->delivery_date = $orderDetail->document_date;
                    } else {
                        $orderDetail->delivery_date = Carbon::today()->format('Y-m-d');
                    }
                }

                // Meta
                $orderDetail->created_by = $this->authUserId;
                $orderDetail->is_migrated = "0";
                $orderDetail->created_at = Carbon::today()->format('Y-m-d');
                $orderDetail->updated_at = Carbon::today()->format('Y-m-d');
                $orderDetail->reason = $errors;
                // Insert into PurchaseOrder
                \App\Models\PurchaseOrderImport::create((array)$orderDetail);
            }
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
