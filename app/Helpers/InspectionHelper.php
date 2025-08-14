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

class InspectionHelper
{
    # Update Mrn details from inspection
    public static function updateMrnDetail($inspection)
    {
        try {
            foreach ($inspection->items as $item) {
                $mrnItem = MrnDetail::find($item->mrn_detail_id);
                if (!$mrnItem) {
                    continue;
                }
                $mrnHeaderId = $mrnItem->mrn_header_id;
                
                // Update quantities
                $mrnItem->accepted_qty += $item->accepted_qty;
                $mrnItem->rejected_qty += $item->rejected_qty;
                $mrnItem->inventory_uom_qty += $item->inventory_uom_qty;
                $mrnItem->accepted_inv_uom_id = $item->accepted_inv_uom_id;
                $mrnItem->accepted_inv_uom_code = $item->accepted_inv_uom_code;
                $mrnItem->accepted_inv_uom_qty += $item->accepted_inv_uom_qty;
                $mrnItem->rejected_inv_uom_id = $item->rejected_inv_uom_id;
                $mrnItem->rejected_inv_uom_code = $item->rejected_inv_uom_code;
                $mrnItem->rejected_inv_uom_qty += $item->rejected_inv_uom_qty;
                $mrnItem->save();
            }

            $mrn = MrnHeader::find($mrnHeaderId);
            // Update MRN stock
            InventoryHelperV2::updateReceiptStock($mrn, $inspection);
            // Update inspection flags on each MRN item
            foreach ($mrn->items as $item) {
                $totalInspected = $item->accepted_qty + $item->rejected_qty;
                $item->is_inspection = ($item->order_qty == $totalInspected) ? 0 : 1;
                $item->save();
            }

            // Final MRN inspection completion flag
            $pendingInspections = $mrn->items()->where('is_inspection', 1)->exists();
            $mrn->is_inspection_completion = $pendingInspections ? 0 : 1;
            $mrn->rejected_sub_store_id = $inspection->rejected_sub_store_id ?? NULL;
            $mrn->save();

            return self::successResponse("MRN details updated successfully.", $mrn);

        } catch (\Exception $e) {
            return self::errorResponse("Error in InspectionHelper@updateMrnDetail: " . $e->getMessage());
        }
    }


    # Handle Mrn calculation update from inspection
    private static function updateMrnCalculation($mrnId) 
    {
        $mrn = MrnHeader::with(['items.itemDiscount', 'expenses', 'shippingAddress'])->find($mrnId);
        if (!$mrn) return;

        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $companyAddress = $organization->addresses->first();

        $companyCountryId = $companyAddress->country_id;
        $companyStateId = $companyAddress->state_id;
        $vendorCountryId = $mrn->billingAddress->country_id ?? null;
        $vendorStateId = $mrn->billingAddress->state_id ?? null;
        
        $totalItemAmount = 0;
        $totalTaxAmount = 0;

        // 1. Calculate item-level discount and amount
        foreach ($mrn->items as $item) {
            $itemTotal = $item->rate * $item->accepted_qty;
            $totalItemAmount += $itemTotal;

            $itemDiscount = $item->itemDiscount->sum('ted_amount');
            $item->discount_amount = $itemDiscount;
            $item->save();
        }

        $totalItemDiscount = $mrn->items->sum('discount_amount');
        $itemValueAfterItemDiscount = $totalItemAmount - $totalItemDiscount;
        $headerDiscount = $mrn->total_header_disc_amount;

        // 2. Calculate header discount, tax, and save per item
        foreach ($mrn->items as $item) {
            $itemPrice = $item->rate * $item->accepted_qty;
            $itemAfterItemDisc = $itemPrice - $item->discount_amount;

            $headerDisc = ($itemValueAfterItemDiscount > 0 && $headerDiscount > 0)
                ? ($itemAfterItemDisc / $itemValueAfterItemDiscount) * $headerDiscount
                : 0;

            $item->header_discount_amount = $headerDisc;
            $priceAfterDiscounts = $itemAfterItemDisc - $headerDisc;

            $taxDetails = TaxHelper::calculateTax(
                $item->hsn_id,
                $priceAfterDiscounts,
                $companyCountryId,
                $companyStateId,
                $vendorCountryId,
                $vendorStateId,
                'sale'
            );

            // Remove old tax TEDs if changed
            $currentTaxIds = array_map('strval', array_column($taxDetails, 'id'));
            $existingTaxIds = MrnExtraAmount::where('mrn_detail_id', $item->id)
                ->where('ted_type', 'Tax')
                ->pluck('ted_id')
                ->map('strval')
                ->toArray();

            sort($currentTaxIds);
            sort($existingTaxIds);

            if ($currentTaxIds !== $existingTaxIds) {
                MrnExtraAmount::where('mrn_detail_id', $item->id)
                    ->where('ted_type', 'Tax')
                    ->delete();
            }

            $itemTax = 0;
            foreach ($taxDetails as $tax) {
                $taxAmount = ((float) $tax['tax_percentage'] / 100) * $priceAfterDiscounts;
                $itemTax += $taxAmount;

                MrnExtraAmount::updateOrCreate(
                    [
                        'mrn_detail_id' => $item->id,
                        'ted_id' => $tax['id'],
                        'ted_type' => 'Tax',
                    ],
                    [
                        'mrn_header_id' => $mrn->id,
                        'ted_level' => 'D',
                        'ted_name' => $tax['tax_type'] ?? null,
                        'assesment_amount' => $item->assessment_amount_total,
                        'ted_percentage' => $tax['tax_percentage'] ?? 0,
                        'ted_amount' => $taxAmount,
                        'applicability_type' => $tax['applicability_type'] ?? 'Collection',
                    ]
                );
            }

            if ($itemTax > 0) {
                $item->tax_value = $itemTax;
                $totalTaxAmount += $itemTax;
            }

            $item->save();
        }

        // 3. Header level expenses
        $totalAfterTaxBeforeExp = $itemValueAfterItemDiscount + $totalTaxAmount - $headerDiscount;
        $headerExpenses = $mrn->expenses->sum('ted_amount');

        foreach ($mrn->items as $item) {
            $baseAmount = ($item->rate * $item->accepted_qty) 
                        - ($item->discount_amount + $item->header_discount_amount) 
                        + ($item->tax_value ?? 0);

            $expenseValue = ($headerExpenses && $totalAfterTaxBeforeExp)
                ? ($baseAmount / $totalAfterTaxBeforeExp) * $headerExpenses
                : 0;

            $item->header_exp_amount = $expenseValue;
            $item->save();
        }

        // 4. Final MRN header update
        $totalDiscount = $mrn->items->sum('discount_amount') + $mrn->items->sum('header_discount_amount');
        $totalExpenses = $mrn->items->sum('header_exp_amount');
        $taxableAmount = $totalItemAmount - $totalDiscount;

        $mrn->update([
            'total_item_amount' => $totalItemAmount,
            'total_discount' => $totalDiscount,
            'taxable_amount' => $taxableAmount,
            'total_taxes' => $totalTaxAmount,
            'total_after_tax_amount' => $taxableAmount + $totalTaxAmount,
            'expense_amount' => $totalExpenses,
            'total_amount' => $taxableAmount + $totalTaxAmount + $totalExpenses,
        ]);

        return $mrn;
    }

    // Success Response
    private static function errorResponse($message)
    {
        return [
            "status" => "error",
            "code" => "500",
            "message" => $message,
            "data" => null,
        ];

    }

    // Error Response
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
