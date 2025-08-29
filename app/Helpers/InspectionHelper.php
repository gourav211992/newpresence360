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
use App\Models\MrnBatchDetail;
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
                $mrnItem->accepted_inv_uom_id = $item->accepted_inv_uom_id;
                $mrnItem->accepted_inv_uom_code = $item->accepted_inv_uom_code;
                $mrnItem->accepted_inv_uom_qty += $item->accepted_inv_uom_qty;
                $mrnItem->rejected_inv_uom_id = $item->rejected_inv_uom_id;
                $mrnItem->rejected_inv_uom_code = $item->rejected_inv_uom_code;
                $mrnItem->rejected_inv_uom_qty += $item->rejected_inv_uom_qty;
                $mrnItem->save();

                // Update item batches
                foreach ($item->batches as $batch) {
                    $batchDetail = MrnBatchDetail::find($batch->batch_detail_id);

                    if ($batchDetail) {
                        $batchDetail->accepted_qty += $batch->accepted_qty;
                        $batchDetail->accepted_inv_uom_qty += $batch->accepted_inv_uom_qty;
                        $batchDetail->rejected_qty += $batch->rejected_qty;
                        $batchDetail->rejected_inv_uom_qty += $batch->rejected_inv_uom_qty;
                        $batchDetail->save();
                    }

                }
            }

            $mrn = MrnHeader::find($mrnHeaderId);
            // Update MRN stock
            $updateReceiptStock = InventoryHelperV2::updateReceiptStock($mrn, $inspection);
            if($updateReceiptStock['status'] == 'error'){
                return self::errorResponse("Error in InspectionHelper@updateMrnDetail: " .$updateReceiptStock['message']);
            }
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
    private static function successResponse($response, $data)
    {
        return [
            "status" => "success",
            "code" => "200",
            "message" => $response,
            "data" => $data
        ];
    }

    // Validate Inspection Checklist
    public static function validateInspectionCheckList(array $inspectionData, object $item)
    {
        if (!$inspectionData) return [
            'message' => 'Inspection Checklist must be filled for item: '. $item->item_name,
            'status' => false
        ];

        if (!is_array($inspectionData) || count($inspectionData) === 0) {
            // dd('dhasdjkhas asdhaskj');
            return [
                'message' => 'Inspection Checklist must be filled for item: '. $item->item_name,
                'status' => false
            ];
        }

        $grouped = collect($inspectionData)->groupBy('detail_id');
        foreach ($grouped as $detailId => $entries) {
            $param = collect($entries)->firstWhere('type', 'parameter_name') ?? $entries->firstWhere('parameter_name');
            $result = collect($entries)->firstWhere('type', 'result') ?? $entries->firstWhere('result');
            if (empty($param['parameter_name'])) {
                return [
                    'message' => 'Parameter name missing in checklist for item: '. $item->item_name,
                    'status' => false
                ];
            }

            if (!isset($result['result']) || !in_array($result['result'], ['pass', 'fail'], true)) {
                return [
                    'message' => 'Pass/Fail (result) missing in checklist for item: '. $item->item_name,
                    'status' => false
                ];
            }
        }

        // ✅ No issues found
        return [
            'message' => 'Success: '. $item->item_name,
            'status' => true
        ];

    }

    /**
     * Validate batch details for an item.
     *
     * @param array $batchDetails Array of batch details to validate.
     * @param object $item The item object containing properties like is_batch_no, is_expiry_date, etc.
     * @return array An associative array with 'status', 'message', and optionally 'data' for normalized rows.
     */
    public static function validateBatches(array $batchDetails, object $item): array
    {
        // 1) Required: non-empty array
        if (empty($batchDetails) || !is_array($batchDetails)) {
            return [
                'status'  => false,
                'message' => 'Batch details are required for item: ' . ($item->item_name ?? ''),
            ];
        }

        // Helpers
        $isBatchNoEnabled   = (int)($item->is_batch_no ?? 0) === 1;
        $isExpiryDateNeeded = $isBatchNoEnabled && (int)($item->is_expiry_date ?? 0) === 1;

        $isValidYear = static function ($y): bool {
            if ($y === null || $y === '') return false;
            if (!preg_match('/^\d{4}$/', (string)$y)) return false;
            $yy = (int)$y;
            $now = (int)date('Y');
            return $yy >= 1900 && $yy <= $now; // adjust if you allow future mfg years
        };

        $isValidDateYmd = static function ($d): bool {
            if (!$d) return false;
            $dt = \DateTime::createFromFormat('Y-m-d', (string)$d);
            return $dt && $dt->format('Y-m-d') === (string)$d;
        };

        $norm = []; // normalized rows to return (optional but handy)
        $eps  = 1e-6;

        foreach ($batchDetails as $i => $row) {
            // index label for friendly messages (1-based)
            $n = $i + 1;

            // Defensive casts
            $batch_number   = trim((string)($row['batch_number']   ?? ''));
            $mrn_qty        = (float)($row['mrn_qty']              ?? null);
            $inspection_qty = (float)($row['inspection_qty']       ?? null);
            $accepted_qty   = (float)($row['accepted_qty']         ?? null);
            $rejected_qty   = isset($row['rejected_qty']) ? (float)$row['rejected_qty'] : null;

            // 2) Mandatory fields: batch_number, mrn_qty, inspection_qty, accepted_qty
            if ($batch_number === '') {
                return [
                    'status'  => false,
                    'message' => "Batch number is required (row {$n}) for item: " . ($item->item_name ?? ''),
                ];
            }
            if (!is_finite($mrn_qty)) {
                return [
                    'status'  => false,
                    'message' => "MRN/Receipt quantity is required (row {$n}) for item: " . ($item->item_name ?? ''),
                ];
            }
            if (!is_finite($inspection_qty)) {
                return [
                    'status'  => false,
                    'message' => "Inspection quantity is required (row {$n}) for item: " . ($item->item_name ?? ''),
                ];
            }
            if (!is_finite($accepted_qty)) {
                return [
                    'status'  => false,
                    'message' => "Accepted quantity is required (row {$n}) for item: " . ($item->item_name ?? ''),
                ];
            }

            // 3) If is_batch_no == 1 → manufacturing_year required, and
            //    if item->is_expiry_date == 1 → expiry_date required (Y-m-d)
            if ($isBatchNoEnabled) {
                // $manufacturing_year = $row['manufacturing_year'] ?? null;
                // if (!$isValidYear($manufacturing_year)) {
                //     return [
                //         'status'  => false,
                //         'message' => "Manufacturing year is required/invalid (row {$n}) for item: " . ($item->item_name ?? ''),
                //     ];
                // }

                if ($isExpiryDateNeeded) {
                    $expiry_date = $row['expiry_date'] ?? null;
                    if (!$isValidDateYmd($expiry_date)) {
                        return [
                            'status'  => false,
                            'message' => "Expiry date is required/invalid (YYYY-MM-DD) (row {$n}) for item: " . ($item->item_name ?? ''),
                        ];
                    }
                }
            }

            // --- Relationship checks (sensible guards) ---
            // inspection must be <= mrn
            if ($inspection_qty - $mrn_qty > $eps) {
                return [
                    'status'  => false,
                    'message' => "Inspection qty cannot exceed Receipt qty (row {$n}) for item: " . ($item->item_name ?? ''),
                ];
            }
            // accepted must be <= inspection
            if ($accepted_qty - $inspection_qty > $eps) {
                return [
                    'status'  => false,
                    'message' => "Accepted qty cannot exceed Inspection qty (row {$n}) for item: " . ($item->item_name ?? ''),
                ];
            }
            // if rejected is provided, it should be inspection - accepted (within epsilon)
            if ($rejected_qty !== null) {
                $expected_rej = max(0.0, $inspection_qty - $accepted_qty);
                if (abs($rejected_qty - $expected_rej) > $eps) {
                    return [
                        'status'  => false,
                        'message' => "Rejected qty must equal (Inspection − Accepted) (row {$n}) for item: " . ($item->item_name ?? ''),
                    ];
                }
            }

            // Build normalized row for caller to persist
            $norm[] = [
                'mrn_batch_detail_id' => $row['mrn_batch_detail_id'] ?? null,
                'batch_number'        => $batch_number,
                'manufacturing_year'  => $row['manufacturing_year'] ?? null,
                'expiry_date'         => $row['expiry_date']        ?? null,
                'mrn_qty'             => $mrn_qty,
                'inspection_qty'      => $inspection_qty,
                'accepted_qty'        => $accepted_qty,
                'rejected_qty'        => $rejected_qty ?? max(0.0, $inspection_qty - $accepted_qty),
            ];
        }

        return [
            'status'  => true,
            'message' => 'Batch details validated for item: ' . ($item->item_name ?? ''),
            'data'    => $norm, // normalized rows (optional but useful to save)
        ];
    }


}
