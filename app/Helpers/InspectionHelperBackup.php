<?php
namespace App\Helpers;

use DB;
use Auth;
use Session;
use stdClass;
use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnAttribute;
use App\Models\MrnItemLocation;
use App\Models\MrnExtraAmount;

use App\Models\InspectionTed;
use App\Models\InspectionHeader;
use App\Models\InspectionDetail;
use App\Models\InspectionItemAttribute;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\GateEntryDetail;

use App\Models\StockLedger;
use App\Models\StockLedgerItemAttribute;

use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Helpers\InventoryHelperV2;
use App\Helpers\ConstantHelper;

class InspectionHelperBackup
{
    # Handle Mrn calculation update from inspection
    public static function updateMrnDetail($inspection) 
    {
        $user = Helper::getAuthenticatedUser();
        $calculateMrn = null;
        $errorMsg = null;
        $successMsg = null;
        $inspectionCompletion = 0;

        try {
            $mrn = MrnHeader::find($inspection->mrn_header_id);
            if (!$mrn) {
                return;
            }

            if($inspection->items && count($inspection->items) > 0) {
                foreach($inspection->items as $item) {
                    $mrn_item = MrnDetail::find($item->mrn_detail_id);
                    if ($mrn_item) {
                        $mrn_item->accepted_qty += $item->accepted_qty;
                        $mrn_item->rejected_qty += $item->rejected_qty;
                        $mrn_item->inventory_uom_qty += $item->inventory_uom_qty;
                        $mrn_item->save();

                        $mrn_item->basic_value = $mrn_item->accepted_qty*$mrn_item->rate;
                        $mrn_item->save();
                        if($item->inspectionTed && count($item->inspectionTed) > 0) {
                            $mrn_item->extraAmounts()->where('mrn_detail_id', $mrn_item->id)->delete();
                            foreach($item->inspectionTed as $ted) {
                                $mrnTed = MrnExtraAmount::create([
                                    'mrn_header_id' => $mrn_item->mrn_header_id,
                                    'mrn_detail_id' => $mrn_item->id,
                                    'ted_id' => $ted->ted_id,
                                    'ted_type' => $ted->ted_type,
                                    'ted_level' => $ted->ted_level,
                                    'ted_name' => $ted->ted_name,
                                    'ted_code' => $ted->ted_code,
                                    'assesment_amount' => $ted->assesment_amount,
                                    'ted_percentage' => $ted->ted_percentage,
                                    'ted_amount' => $ted->ted_amount,
                                    'applicability_type' => $ted->applicability_type,
                                ]);  
                            }
                        }
                    }
                }
    
                $calculateMrn = self::updateMrnCalculation($mrn->id);
                if($calculateMrn) {
                    $receiptStock = InventoryHelperV2::updateReceiptStock($mrn);
                }

                foreach($mrn->items as $mrn_item) { 
                    $inspectionQty = $mrn_item->accepted_qty + $mrn_item->rejected_qty;
                    if(($mrn_item->order_qty == $inspectionQty)) {
                        $mrn_item->is_inspection = 0;
                    } else {
                        $mrn_item->is_inspection = 1;
                    }
                    $mrn_item->save();
                }

                $inspectionMrn = $mrn->items()->where('is_inspection', 1)->count();
                if($inspectionMrn == 0) {
                    $mrn->is_inspection_completion = 1;
                    $mrn->save();
                } else {
                    $mrn->is_inspection_completion = 0;
                    $mrn->save();
                }
            }

            $message = "MRN details updated successfully.";
            $data = self::successResponse($message, $mrn);
            return $data;
        } catch (\Exception $e) {
            $errorMsg = "Error in InspectionHelper@updateMrnDetail: " . $e->getMessage();
            return self::errorResponse($errorMsg);
        }
    }

    # Handle Mrn calculation update from inspection
    private static function updateMrnCalculation($mrnId) 
    {
        $mrn = MrnHeader::find($mrnId);
        if (!$mrn) {
            return;
        }

        $totalItemAmnt = 0;
        $totalTaxAmnt = 0;
        $totalItemValue = 0.00;
        $totalTaxValue = 0.00;
        $totalDiscValue = 0.00;
        $totalExpValue = 0.00;
        $totalItemLevelDiscValue = 0.00;
        $totalAmount = 0.00;
        $vendorShippingCountryId = $mrn->shippingAddress->country_id;
        $vendorShippingStateId = $mrn->shippingAddress->state_id;

        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $firstAddress = $organization->addresses->first();
        $companyCountryId = $firstAddress->country_id;
        $companyStateId = $firstAddress->state_id;

        # Save Item level discount
        foreach($mrn->items as $mrn_item) {
            $itemPrice = $mrn_item->rate*$mrn_item->accepted_qty;
            $totalItemAmnt = $totalItemAmnt + $itemPrice; 
            $itemDis = $mrn_item->itemDiscount()->sum('ted_amount');
            $mrn_item->discount_amount = $itemDis;
            $mrn_item->save();
        }
        # Save header level discount
        $totalItemValue = $mrn->total_item_amount;
        $totalItemValueAfterTotalItemDisc = $mrn->total_item_amount - $mrn->items()->sum('discount_amount');
        $totalHeaderDiscount = $mrn->total_header_disc_amount;

        foreach($mrn->items as $mrn_item) {
            $itemPrice = $mrn_item->rate*$mrn_item->accepted_qty;
            $itemPriceAfterItemDis = $itemPrice - $mrn_item->discount_amount;
            # Calculate header discount
            // Calculate and save header discount
            if ($totalItemValueAfterTotalItemDisc > 0 && $totalHeaderDiscount > 0) {
                $headerDis = ($itemPriceAfterItemDis / $totalItemValueAfterTotalItemDisc) * $totalHeaderDiscount;
            } else {
                $headerDis = 0;
            }
            $mrn_item->header_discount_amount = $headerDis;
            
            # Calculate header expenses
            $priceAfterBothDis = $itemPriceAfterItemDis - $headerDis;
            $taxDetails = TaxHelper::calculateTax($mrn_item->hsn_id, $priceAfterBothDis, $companyCountryId, $companyStateId, $vendorShippingCountryId, $vendorShippingStateId, 'sale');
            if (isset($taxDetails) && count($taxDetails) > 0) {
                $itemTax = 0;
                $cTaxDeIds = array_column($taxDetails, 'id');
                $existTaxIds = MrnExtraAmount::where('mrn_detail_id', $mrn_item->id)
                                ->where('ted_type','Tax')
                                ->pluck('ted_id')
                                ->toArray();

                $array1 = array_map('strval', $existTaxIds);
                $array2 = array_map('strval', $cTaxDeIds);
                sort($array1);
                sort($array2);

                if($array1 != $array2) {
                    # Changes
                    MrnExtraAmount::where("mrn_detail_id",$mrn_item->id)
                        ->where('ted_type','Tax')
                        ->delete();
                }

                foreach ($taxDetails as $taxDetail) {
                    $itemTax += ((double)$taxDetail['tax_percentage']/100*$priceAfterBothDis);

                    $ted = MrnExtraAmount::firstOrNew([
                        'mrn_detail_id' => $mrn_item->id,
                        'ted_id' => $taxDetail['id'],
                        'ted_type' => 'Tax',
                    ]);

                    $ted->mrn_header_id = $mrn->id;
                    $ted->mrn_detail_id = $mrn_item->id;
                    $ted->ted_type = 'Tax';
                    $ted->ted_level = 'D';
                    $ted->ted_id = $taxDetail['id'] ?? null;
                    $ted->ted_name = $taxDetail['tax_type'] ?? null;
                    $ted->assesment_amount = $mrn_item->assessment_amount_total;
                    $ted->ted_percentage = $taxDetail['tax_percentage'] ?? 0.00;
                    $ted->ted_amount = ((double)$taxDetail['tax_percentage']/100*$priceAfterBothDis) ?? 0.00;
                    $ted->applicability_type = $taxDetail['applicability_type'] ?? 'Collection';
                    $ted->save();
                }
                if($itemTax) {
                    $mrn_item->tax_value = $itemTax;
                    $mrn_item->save();
                    $totalTaxAmnt = $totalTaxAmnt + $itemTax;
                }
            }
            $mrn_item->save();
        }

        # Save expenses
        $totalValueAfterBothDis = $totalItemValueAfterTotalItemDisc + $totalTaxAmnt - $totalHeaderDiscount;
        $headerExpensesTotal = $mrn->expenses()->sum('ted_amount'); 
        if ($headerExpensesTotal) {
            foreach($mrn->items as $mrn_item) { 
                $itemPriceAterBothDis = ($mrn_item->rate*$mrn_item->accepted_qty) - ($mrn_item->header_discount_amount + $mrn_item->discount_amount) + $mrn_item->tax_value;
                $exp = $itemPriceAterBothDis / $totalValueAfterBothDis * $headerExpensesTotal;
                $mrn_item->header_exp_amount = $exp;
                $mrn_item->save();
            }
        } else {
            foreach($mrn->items as $mrn_item) { 
                $mrn_item->header_exp_amount = 0.00;
                $mrn_item->save();
            }
        }

        /*Update Calculation*/
        $totalDiscValue = $mrn->items()->sum('header_discount_amount') + $mrn->items()->sum('discount_amount');
        $totalExpValue = $mrn->items()->sum('header_exp_amount');
        $mrn->total_item_amount = $totalItemAmnt;
        $mrn->total_discount = $totalDiscValue;
        $mrn->taxable_amount = ($totalItemAmnt - $totalDiscValue);
        $mrn->total_taxes = $totalTaxAmnt;
        $mrn->total_after_tax_amount = (($totalItemAmnt - $totalDiscValue) + $totalTaxAmnt);
        $mrn->expense_amount = $totalExpValue;
        $totalAmount = (($totalItemAmnt - $totalDiscValue) + ($totalTaxAmnt + $totalExpValue));
        $mrn->total_amount = $totalAmount;
        $mrn->save();

        return $mrn;
    }

    private static function errorResponse($message)
    {
        return [
            "status" => "error",
            "code" => "500",
            "message" => $message,
            "data" => null,
        ];

    }

    private static function successResponse($response,$data)
    {
        return [
            "status" => "success",
            "code" => "200",
            "message" => $response,
            "data" => $data
        ];
    }

}
