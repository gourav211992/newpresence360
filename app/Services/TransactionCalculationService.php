<?php 
namespace App\Services;

use Illuminate\Http\Request;

use App\Models\GateEntryTed;
use App\Models\GateEntryHeader;
use App\Models\GateEntryDetail;

use App\Models\MrnDetail;
use App\Models\MrnHeader;
use App\Models\MrnExtraAmount;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\VendorAsnItem;
use App\Models\ErpSoJobWorkItem;
use App\Models\JobOrder\JoProduct;

use App\Models\Organization;

use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class TransactionCalculationService
{
    // Validate the quantity of items in MRN against various conditions.
    //  *
    //  * @param Request $request
    //  * @return array
    //  */
    

    // -------------------------------
    // GE Calculation (optimized)
    // -------------------------------
    public static function updateGECalculation($geDetail)
    {
        $geHeader = GateEntryHeader::query()
            ->with([
                'items.itemDiscount',
                'expenses',
                'billingAddress',
                'shippingAddress',
            ])
            ->find($geDetail->header_id);

        if (!$geHeader) {
            return self::errorResponse('Gate Entry Header not found', ['detail_id' => $geDetail->id ?? null]);
        }

        // Company/vendor geo
        $user         = Helper::getAuthenticatedUser();
        // $organization = $user->organization ?? null;
        // $companyAddr  = $organization?->addresses?->first();
        // $companyCountryId = $companyAddr->country_id ?? null;
        // $companyStateId   = $companyAddr->state_id ?? null;


        $organization = Organization::where('id', $user->organization_id)->first();
        //Tax Country and State
        $firstAddress = $organization->addresses->first();
        $companyCountryId = null;
        $companyStateId = null;
        if ($firstAddress) {
            $companyCountryId = $firstAddress->country_id;
            $companyStateId = $firstAddress->state_id;
        }


        $vendorCountryId = $geHeader->billingAddress->country_id ?? $geHeader->shippingAddress->country_id ?? null;
        $vendorStateId   = $geHeader->billingAddress->state_id   ?? $geHeader->shippingAddress->state_id   ?? null;

        $items = $geHeader->items;

        // 1) Precompute raw totals & item-discounts (keep temps in arrays)
        $rawTotals   = []; // detail_id => raw total
        $itemDiscMap = []; // detail_id => item discount

        $totalItemAmount   = 0.0;
        $totalItemDiscount = 0.0;

        foreach ($items as $it) {
            $raw = (float)$it->rate * (float)$it->accepted_qty;
            $disc = (float)$it->itemDiscount->sum('ted_amount');

            $rawTotals[$it->id]   = $raw;
            $itemDiscMap[$it->id] = $disc;

            $totalItemAmount   += $raw;
            $totalItemDiscount += $disc;

            // stage on model only *real* columns
            $it->discount_amount = $disc;
        }

        $itemValueAfterItemDiscount = $totalItemAmount - $totalItemDiscount;
        $headerDiscountTotal        = (float)($geHeader->total_header_disc_amount ?? 0);

        // 2) Header discount proportion + TAX upsert/prune
        $totalTaxAmount = 0.0;
        $now = now();

        foreach ($items as $it) {
            $raw  = $rawTotals[$it->id] ?? 0.0;
            $disc = $itemDiscMap[$it->id] ?? 0.0;

            $afterItemDisc = $raw - $disc;

            $headerDisc = ($itemValueAfterItemDiscount > 0 && $headerDiscountTotal > 0)
                ? ($afterItemDisc / $itemValueAfterItemDiscount) * $headerDiscountTotal
                : 0.0;

            $it->header_discount_amount = $headerDisc;

            $priceAfterDiscounts = $afterItemDisc - $headerDisc;

            $taxDetails = TaxHelper::calculateTax(
                $it->hsn_id,
                $priceAfterDiscounts,
                $companyCountryId,
                $companyStateId,
                $vendorCountryId,
                $vendorStateId,
                'sale'
            );
            if (!is_array($taxDetails)) $taxDetails = [];

            $rows    = [];
            $keepIds = [];
            $itemTax = 0.0;

            foreach ($taxDetails as $tax) {
                $tid       = (string)($tax['id'] ?? '');
                $perc      = (float)($tax['tax_percentage'] ?? 0);
                $taxAmount = ($perc / 100) * $priceAfterDiscounts;

                $itemTax  += $taxAmount;
                $keepIds[] = $tid;

                $rows[] = [
                    'detail_id'          => $it->id,
                    'header_id'          => $geHeader->id,
                    'ted_id'             => $tax['id'],
                    'ted_type'           => 'Tax',
                    'ted_level'          => 'D',
                    'ted_name'           => $tax['tax_type'] ?? null,
                    'assesment_amount'   => $it->assessment_amount_total,
                    'ted_percentage'     => $perc,
                    'ted_amount'         => $taxAmount,
                    'applicability_type' => $tax['applicability_type'] ?? 'Collection',
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            if (!empty($rows)) {
                GateEntryTed::upsert(
                    $rows,
                    ['detail_id','ted_id','ted_type'],
                    ['header_id','ted_level','ted_name','assesment_amount','ted_percentage','ted_amount','applicability_type','updated_at']
                );
            }

            GateEntryTed::where('detail_id', $it->id)
                ->where('ted_type', 'Tax')
                ->when(!empty($keepIds), fn($q) => $q->whereNotIn('ted_id', $keepIds))
                ->delete();

            $it->tax_value = $itemTax;
            $totalTaxAmount += $itemTax;
        }

        // 3) Header expenses proportional
        $totalAfterTaxBeforeExp = ($totalItemAmount - $totalItemDiscount - $headerDiscountTotal) + $totalTaxAmount;
        $headerExpensesTotal    = (float)$geHeader->expenses->sum('ted_amount');
        $den = $totalAfterTaxBeforeExp > 0 ? $totalAfterTaxBeforeExp : 1.0;

        foreach ($items as $it) {
            $raw  = $rawTotals[$it->id] ?? 0.0;
            $disc = $itemDiscMap[$it->id] ?? 0.0;

            $base = $raw - ((float)$it->discount_amount + (float)$it->header_discount_amount) + ((float)$it->tax_value);
            $share = ($headerExpensesTotal > 0) ? ($base / $den) * $headerExpensesTotal : 0.0;

            $it->header_exp_amount = $share;

            // save only real columns when changed
            if ($it->isDirty(['discount_amount','header_discount_amount','tax_value','header_exp_amount'])) {
                $it->save();
            }
        }

        // 4) Header totals (single update)
        $totalDiscount = (float)($items->sum('discount_amount') + $items->sum('header_discount_amount'));
        $totalTaxes    = (float)$items->sum('tax_value');
        $totalExpenses = (float)$items->sum('header_exp_amount');
        $taxableAmount = $totalItemAmount - $totalDiscount;
        $afterTax      = $taxableAmount + $totalTaxes;
        $grandTotal    = $afterTax + $totalExpenses;

        $geHeader->update([
            'total_item_amount'      => $totalItemAmount,
            'total_discount'         => $totalDiscount,
            'taxable_amount'         => $taxableAmount,
            'total_taxes'            => $totalTaxes,
            'total_after_tax_amount' => $afterTax,
            'expense_amount'         => $totalExpenses,
            'total_amount'           => $grandTotal,
        ]);

        // Back-update related
        if ($geHeader->reference_type === 'po') {
            $geDetail->poItem?->update(['ge_qty' => $geDetail->accepted_qty]);
            $geDetail->asnItem?->update(['ge_qty' => $geDetail->accepted_qty]);
        } elseif ($geHeader->reference_type === 'jo') {
            $geDetail->joProduct?->update(['ge_qty' => $geDetail->accepted_qty]);
            $geDetail->asnItem?->update(['ge_qty' => $geDetail->accepted_qty]);
        }

        return self::successResponse('Calculation Updated', ['data' => $geDetail]);
    }


    // -------------------------------
    // GRN Calculation (optimized)
    // -------------------------------
    public static function updateMrnCalculation($mrnId)
    {
        /** @var \App\Models\MrnHeader|null $mrn */
        $mrn = MrnHeader::query()
            ->with([
                'items.itemDiscount',   // item-level TEDs for discount
                'expenses',             // header-level expenses
                'billingAddress',       // vendor geo (preferred)
                'shippingAddress',      // vendor geo fallback
            ])
            ->find($mrnId);

        if (!$mrn) {
            return null;
        }

        // ---- Company geo (guard against missing address) ------------------------
        $user         = Helper::getAuthenticatedUser();
        $organization = $user->organization ?? null;
        $companyAddr  = $organization?->addresses?->first();

        $companyCountryId = $companyAddr->country_id ?? null;
        $companyStateId   = $companyAddr->state_id ?? null;

        // Vendor geo: prefer billing, fallback shipping
        $vendorCountryId = $mrn->billingAddress->country_id ?? $mrn->shippingAddress->country_id ?? null;
        $vendorStateId   = $mrn->billingAddress->state_id   ?? $mrn->shippingAddress->state_id   ?? null;

        // ---- 1) Item raw totals + item-level discounts --------------------------
        $items = $mrn->items; // Collection<MrnDetail>

        $totalItemAmount   = 0.0;
        $totalItemDiscount = 0.0;

        foreach ($items as $it) {
            $rawTotal = (float)$it->rate * (float)$it->accepted_qty;
            $itemDisc = (float)$it->itemDiscount->sum('ted_amount');

            $totalItemAmount   += $rawTotal;
            $totalItemDiscount += $itemDisc;

            // stage values in-memory; we’ll save only if dirty later
            $it->discount_amount = $itemDisc;
            $it->tmp_raw_total   = $rawTotal; // temp for later reuse
        }

        $itemValueAfterItemDiscount = $totalItemAmount - $totalItemDiscount;
        $headerDiscountTotal        = (float)($mrn->total_header_disc_amount ?? 0);

        // ---- 2) Per-item header discount + taxes (upsert + prune) ---------------
        $totalTaxAmount = 0.0;
        $now = now();

        foreach ($items as $it) {
            $itemAfterItemDisc = $it->tmp_raw_total - (float)$it->discount_amount;

            $headerDisc = ($itemValueAfterItemDiscount > 0 && $headerDiscountTotal > 0)
                ? ($itemAfterItemDisc / $itemValueAfterItemDiscount) * $headerDiscountTotal
                : 0.0;

            $it->header_discount_amount = $headerDisc;

            $priceAfterDiscounts = $itemAfterItemDisc - $headerDisc;

            // Compute taxes for this item
            $taxDetails = TaxHelper::calculateTax(
                $it->hsn_id,
                $priceAfterDiscounts,
                $companyCountryId,
                $companyStateId,
                $vendorCountryId,
                $vendorStateId,
                'sale' // NOTE: keep as-is; switch to 'purchase' if your tax logic expects it
            );
            if (!is_array($taxDetails)) $taxDetails = [];

            // Build upsert rows + track IDs to keep
            $rows    = [];
            $keepIds = [];
            $itemTax = 0.0;

            foreach ($taxDetails as $tax) {
                $tid       = (string)($tax['id'] ?? '');
                $perc      = (float)($tax['tax_percentage'] ?? 0);
                $taxAmount = ($perc / 100) * $priceAfterDiscounts;

                $itemTax  += $taxAmount;
                $keepIds[] = $tid;

                $rows[] = [
                    'mrn_detail_id'      => $it->id,
                    'mrn_header_id'      => $mrn->id,
                    'ted_id'             => $tax['id'],
                    'ted_type'           => 'Tax',
                    'ted_level'          => 'D',
                    'ted_name'           => $tax['tax_type'] ?? null,
                    'assesment_amount'   => $it->assessment_amount_total,
                    'ted_percentage'     => $perc,
                    'ted_amount'         => $taxAmount,
                    'applicability_type' => $tax['applicability_type'] ?? 'Collection',
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            // Upsert taxes for this item (1 query)
            if (!empty($rows)) {
                MrnExtraAmount::upsert(
                    $rows,
                    ['mrn_detail_id', 'ted_id', 'ted_type'],
                    ['mrn_header_id','ted_level','ted_name','assesment_amount','ted_percentage','ted_amount','applicability_type','updated_at']
                );
            }

            // Prune stale taxes in a single delete
            MrnExtraAmount::where('mrn_detail_id', $it->id)
                ->where('ted_type', 'Tax')
                ->when(!empty($keepIds), fn($q) => $q->whereNotIn('ted_id', $keepIds))
                ->delete();

            $it->tax_value = $itemTax;
            $totalTaxAmount += $itemTax;
        }

        // ---- 3) Header-level expenses: proportional share -----------------------
        $totalAfterTaxBeforeExp = ($totalItemAmount - $totalItemDiscount - $headerDiscountTotal) + $totalTaxAmount;
        $headerExpensesTotal    = (float)$mrn->expenses->sum('ted_amount');
        $den = $totalAfterTaxBeforeExp > 0 ? $totalAfterTaxBeforeExp : 1.0;

        foreach ($items as $it) {
            $baseAmount = $it->tmp_raw_total
                        - ((float)$it->discount_amount + (float)$it->header_discount_amount)
                        + ((float)$it->tax_value);

            $share = ($headerExpensesTotal > 0) ? ($baseAmount / $den) * $headerExpensesTotal : 0.0;

            $it->header_exp_amount = $share;

            // Persist only if changed
            if ($it->isDirty(['discount_amount','header_discount_amount','tax_value','header_exp_amount'])) {
                $it->save();
            }

            unset($it->tmp_raw_total); // cleanup temp
        }

        // ---- 4) Final MRN header totals (single update) -------------------------
        $totalDiscount = (float)($items->sum('discount_amount') + $items->sum('header_discount_amount'));
        $totalExpenses = (float)$items->sum('header_exp_amount');
        $taxableAmount = $totalItemAmount - $totalDiscount;
        $afterTax      = $taxableAmount + $totalTaxAmount;
        $grandTotal    = $afterTax + $totalExpenses;

        $mrn->update([
            'total_item_amount'      => $totalItemAmount,
            'total_discount'         => $totalDiscount,
            'taxable_amount'         => $taxableAmount,
            'total_taxes'            => $totalTaxAmount,
            'total_after_tax_amount' => $afterTax,
            'expense_amount'         => $totalExpenses,
            'total_amount'           => $grandTotal,
        ]);

        return self::successResponse('Calculation Updated', ['data' => $mrn]);

    }


    // -------------------------------
    // Response helpers (unchanged)
    // -------------------------------
    private static function errorResponse($message, $detail)
    {
        return [
            "code"    => "500",
            "status"  => "error",
            "data"    => $detail,
            "message" => $message,
        ];
    }

    private static function successResponse($response, $detail)
    {
        return [
            "code"    => "200",
            "status"  => "success",
            "data"    => $detail,
            "message" => $response,
        ];
    }

}