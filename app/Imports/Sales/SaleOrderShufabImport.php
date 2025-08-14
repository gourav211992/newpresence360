<?php

namespace App\Imports\Sales;
use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Helpers\SaleModuleHelper;
use App\Models\Attribute;
use App\Models\Book;
use App\Models\Customer;
use App\Models\ErpSaleOrder;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\SaleOrderImportShufab;
use App\Models\SoImportShufabDynField;
use App\Models\SubType;
use App\Models\Unit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use stdClass;

class SaleOrderShufabImport implements ToArray, WithHeadingRow, SkipsEmptyRows, WithChunkReading
{
    private $bookId = null;
    private $locationId = null;
    private $authUserId = null;
    public function __construct(int $bookId, int $locationId, int $authUserId)
    {
        //Assign Book and Location Id
        $this -> bookId = $bookId;
        $this -> locationId = $locationId;
        $this -> authUserId = $authUserId;
    }
    public function array(array $rows)
    {
        //Book and Location Validation
        $book = Book::find($this -> bookId);
        //Location Details
        $location = ErpStore::find($this -> locationId);
        $companyCountryId = null;
        $companyStateId = null;
        $locationAddress = $location ?-> address;
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex) {
                $orderDetail = new stdClass();
                $errors = [];
                //Book and Location Validation
                if (!isset($book)) {
                    $errors[] = 'Invalid Book';
                }
                if ($location && isset($locationAddress)) {
                    $companyCountryId = $location->address?->country_id??null;
                    $companyStateId = $location->address?->state_id??null;
                } else {
                    $errors[] = 'Invalid Location or location address not available';
                }
                //Order No Validation
                $orderDetail -> order_no = $row['order_no'];
                if (!$orderDetail -> order_no) {
                    $errors[] = "Document Number not specified";
                }
                //Order Date Validation
                if ($row['order_date']) {
                    $orderDetail -> document_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['order_date'])->format('Y-m-d');
                    if (!$orderDetail -> document_date) {
                        $orderDetail -> document_date = Carbon::today() -> format("Y-m-d");
                    }
                } else {
                    $orderDetail -> document_date = Carbon::today() -> format("Y-m-d");
                }
                //Document Number Validation
                $numberPatternData = Helper::generateDocumentNumberNew($book->id, $orderDetail -> document_date);
                if (!isset($numberPatternData)) {
                    $errors[] = 'Number Pattern for Series not specified';
                }
                $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $orderDetail -> order_no;
                $regeneratedDocExist = ErpSaleOrder::withDefaultGroupCompanyOrg() -> where('book_id',$book -> id)
                ->where('document_number',$document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    $errors[] = "Duplicate Document Number";
                }
                $bookParams = BookHelper::fetchBookDocNoAndParameters($this -> bookId, $orderDetail -> document_date);
                $parameters = $bookParams['data']['parameters'];
                if (isset($parameters -> future_date_allowed) && $parameters -> future_date_allowed == 'no') {
                    //Check for Future date
                    if (Carbon::parse($orderDetail -> document_date) -> gt(Carbon::today())) {
                        $errors[] = "Future Date is not allowed";
                    }
                }
                $bookParams = BookHelper::fetchBookDocNoAndParameters($this -> bookId, $orderDetail -> document_date);
                $parameters = $bookParams['data']['parameters'];
                if (isset($parameters -> future_date_allowed[0]) && $parameters -> future_date_allowed[0] == 'no') {
                    //Check for Future date
                    if (Carbon::parse($orderDetail -> document_date) -> gt(Carbon::today())) {
                        $errors[] = "Future Order Date is not allowed";
                    }
                }

                if (isset($parameters -> back_date_allowed[0]) && $parameters -> back_date_allowed[0] == 'no') {
                    //Check for past date
                    if (Carbon::parse($orderDetail -> document_date) -> lt(Carbon::today())) {
                        $errors[] = "Past Order Date is not allowed";
                    }
                }
                //Customer Validation
                $orderDetail -> customer_code = $row['customer_code'];
                if (!$orderDetail -> customer_code) {
                    $errors[] = "Customer not specified";
                }
                //Trim and lower the customer code/ name entered for flexible search
                $customerSearch = strtolower(trim($orderDetail->customer_code));
                $customer = Customer::withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE) 
                    -> where('customer_type', ConstantHelper::REGULAR) 
                    -> where(function ($query) use ($customerSearch) {
                        $query->whereRaw('LOWER(company_name) = ?', [$customerSearch])
                              ->orWhereRaw('LOWER(customer_code) = ?', [$customerSearch]);
                    }) -> first();
                if (!isset($customer)) {
                    $errors[] = "Customer not found or is inactive";
                } else {
                    //Customer exchange rate
                    $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($customer -> currency_id, $orderDetail -> document_date);
                    if ($currencyExchangeData['status'] == false) {
                        $errors[] =  $currencyExchangeData['message'];
                    }
                    //Customer Addresses
                    $customerAddresses = $customer -> addresses;
                    $customerBillAddress = $customerAddresses -> whereIn('type', ['billing', 'both']) -> first();
                    if (!isset($customerBillAddress)) {
                        $errors[] =  'Customer Billing Adddress not found';
                    }
                    $customerShipAddress = $customerAddresses -> whereIn('type', ['shipping', 'both']) -> first();
                    if (!isset($customerShipAddress)) {
                        $errors[] =  'Customer Shipping Adddress not found';
                    }
                }
                $orderDetail -> customer_id = $customer ?-> id;
                //Consignee Name
                $orderDetail -> consignee_name = $row['consignee_name'] ?? null;
                //Item
                $totalQty = 0;
                $orderDetail -> item_code = $row['item_code'];
                if (!$orderDetail -> item_code) {
                    $errors[] = "Item not specified";
                }
                //Trim and lower the item code/ name entered for flexible search
                $itemSearch = strtolower(trim($orderDetail->item_code));
                //Get only limited type of items
                $subTypeIds = SubType::whereIn('name', [ConstantHelper::FINISHED_GOODS, ConstantHelper::TRADED_ITEM, 
                ConstantHelper::ASSET,ConstantHelper::WIP_SEMI_FINISHED])
                -> get() -> pluck('id') -> toArray();
                $item = Item::withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE) 
                    -> whereHas('subTypes', function ($subTypeQuery) use($subTypeIds) {
                        $subTypeQuery -> whereIn('sub_type_id', $subTypeIds);
                    }) -> where('type', ConstantHelper::GOODS) 
                    ->where(function ($query) use ($itemSearch) {
                        $query->whereRaw('LOWER(item_code) = ?', [$itemSearch])
                              ->orWhereRaw('LOWER(item_name) = ?', [$itemSearch]);
                    }) -> first();
                $orderDetail -> item_id = $item ?-> id;
                if (!isset($item)) {
                    $errors[] = "Item not found or invalid item specified";
                }
                //UOM
                $orderDetail -> uom_code = $row['uom'];
                if ($orderDetail -> uom_code) {
                    $itemUoms = [];
                    //Get Item specific UOMs
                    if ($item) {
                        array_push($itemUoms, $item -> uom_id);
                        foreach ($item -> alternateUOMs as $altUom) {
                            array_push($itemUoms, $altUom -> uom_id);
                        }
                    }
                    $uomSearch = strtolower(trim($orderDetail -> uom_code));
                    $uom = Unit::withDefaultGroupCompanyOrg() -> where('status', ConstantHelper::ACTIVE) 
                    -> whereRaw('LOWER(name) = ?',[$uomSearch]) 
                    -> when(count($itemUoms), function ($itemUomQuery) use($itemUoms) {
                        $itemUomQuery -> whereIn('id', $itemUoms);
                    }) -> first();
                    $orderDetail -> uom_id = $uom ?-> id;
                    if (!$uom) {
                        $errors[] = "UOM not found";
                    }
                } else {
                    //Assign Default UOM
                    if ($item) {
                        $itemUom = $item -> uom;
                        $itemSellingUom = null;
                        //Check and assign if a selling UOM exists
                        foreach ($item -> alternateUOMs as $altUom) {
                            if ($altUom -> is_selling) {
                                $itemSellingUom = $altUom -> uom;
                            }
                        }
                        //Assign if selling uom is found else default to item uom
                        if (isset($itemSellingUom)) {
                            $orderDetail -> uom_code = $itemSellingUom -> alias;
                            $orderDetail -> uom_id = $itemSellingUom -> id;
                        } else if (isset($itemUom)) {
                            $orderDetail -> uom_code = $itemUom -> alias;
                            $orderDetail -> uom_id = $itemUom -> id;
                        } else {
                            $errors[] = "No UOM found for this item";
                        }
                    }
                }             
                //Attribute Size
                for ($i=1; $i <= 14; $i++) { 
                    $key = 'size_' . $i;
                    $currentQty = floatval($row[$key]);
                    if ($currentQty > 0) {
                        //Check if Item Bom Exists
                        $attribute = Attribute::whereHas('attributeGroup', function ($groupQuery) {
                            $groupQuery -> withDefaultGroupCompanyOrg() -> whereRaw('LOWER(name) = ?', ['size']);
                        }) -> where('value', $i) -> first();
                        if (!$attribute) {
                            $errors[] = "Item Attribute Size - $i not found";
                        } else {
                            $attributesArray = [
                                'attribute_id' => $attribute -> id,
                                'attribute_value' => $i
                            ];
                            //Check BOM
                            $bomDetails = ItemHelper::checkItemBomExists($orderDetail -> item_id ?? 0, $attributesArray);
                            if (!isset($bomDetails['bom_id'])) {
                                $errors[] = "Bom not found";
                            }
                        }
                        $orderDetail -> {$key} = $currentQty;
                        $totalQty += $orderDetail -> {$key};
                    } else {
                        $orderDetail -> {$key} = 0;
                    }
                }
                if ($totalQty <= 0) {
                    $errors[] = 'Please specify item quantity';
                }
                //Rate
                if ($row['rate'] && floatval($row['rate']) > 0) {
                    $orderDetail -> rate = $row['rate'];
                } else {
                    if ($item) {
                        $itemRate = SaleModuleHelper::getItemSellingPrice($item, $orderDetail -> uom_id);
                        $orderDetail -> rate = $itemRate;
                        if (floatval($orderDetail -> rate) <= 0) {
                            $errors[] = "Item Rate not specified and not found from Item";
                        }
                    }
                }
                if (!isset($orderDetail -> rate)) {
                    $errors[] = "Item Rate not specified or not found from Item";
                }
                //Delivery Date
                if ($row['delivery_date']) {
                    $orderDetail -> delivery_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['delivery_date'])->format('Y-m-d');
                    if (Carbon::parse($orderDetail -> delivery_date) -> lt(Carbon::today())) {
                        $errors[] = "Past Delivery Date is not allowed";
                    }
                } else {
                    //Check if document date is current or future
                    if (Carbon::parse($orderDetail -> document_date) -> gte(Carbon::today())) {
                        $orderDetail -> delivery_date = $orderDetail -> document_date;
                    } else { //Default to current date
                        $orderDetail -> delivery_date = Carbon::today() -> format('Y-m-d');
                    }
                }
                $orderDetail -> created_by = $this -> authUserId;
                $orderDetail -> is_migrated = "0";
                $orderDetail -> created_at = Carbon::today() -> format('Y-m-d');
                $orderDetail -> updated_at = Carbon::today() -> format('Y-m-d');
                $orderDetail -> reason = $errors;
                //Sales Order Insertion
                $soImport = SaleOrderImportShufab::create((array) $orderDetail);
                //Dynamic Fields
                $dynamicFields = $book ?-> dynamic_fields ?? [];
                foreach ($dynamicFields as $dynField) {
                    foreach ($dynField -> dynamic_field -> details as $dynDetail) {
                        $trimmedField = strtolower(trim($dynDetail -> name));
                        $excelField = str_replace(" ", "_", $trimmedField);
                        //Excel Field
                        SoImportShufabDynField::create([
                            'import_id' => $soImport -> id,
                            'dyn_header_id' => $dynDetail -> header_id,
                            'dyn_detail_id' => $dynDetail -> id,
                            'name' => $dynDetail -> name,
                            'value' => isset($row[$excelField]) ? $row[$excelField] : null
                        ]);
                    }
                }
            }
        }
        
    }
    public function chunkSize() : int
    {
        return 100;
    }
}
