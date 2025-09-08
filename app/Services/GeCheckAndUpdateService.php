<?php
namespace App\Services;

use Illuminate\Http\Request;

use App\Models\GateEntryTed;
use App\Models\GateEntryHeader;
use App\Models\GateEntryDetail;
use App\Models\GateEntryExtraAmount;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\MrnDetail;
use App\Models\VendorAsnItem;
use App\Models\ErpSoJobWorkItem;
use App\Models\JobOrder\JoProduct;

use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class GeCheckAndUpdateService
{
    // Validate the quantity of items in MRN against various conditions.
    //  *
    //  * @param Request $request
    //  * @return array
    //  */
    public function validateOrderQuantity($inputData)
    {
        $item = Item::find($inputData['item_id']);
        $type = $inputData['type'];
        $inputQty = (float) $inputData['qty'] ?? 0.00;

        if (!$item) {
            return self::errorResponse("Item not found.", [
                'order_qty' => $inputQty
            ]);
        }

        // === Case 1: Edit (MRN Detail exists) ===
        if (!empty($inputData['ge_detail_id'])) {
            $geDetail = GateEntryDetail::find($inputData['ge_detail_id']);
            $geOrderQty = number_format((float) $geDetail->accepted_qty ?? 0.00, 2);
            if (!$geDetail) {
                return self::errorResponse("Ge Item not found.", [
                    'order_qty' => $geOrderQty
                ]);
            }

            if ($geDetail->mrn_qty > $inputQty) {
                $mrnQty = number_format((float) $geDetail->mrn_qty, 2);
                return self::errorResponse("Order qty cannot be less than mrn quantity ({$mrnQty}) as it has already been used.", [
                    'order_qty' => $geOrderQty
                ]);
            }

            $poDetail = match ($type) {
                ConstantHelper::JO_SERVICE_ALIAS => JoProduct::find($inputData['jo_detail_id']),
                default => PoItem::find($inputData['po_detail_id'])
            };

            if ($poDetail) {
                $availableQty = floatval($poDetail->order_qty - $poDetail->ge_qty);
                $inputDiff = $inputQty - floatval($geDetail->accepted_qty);

                if ($inputQty > $poDetail->order_qty) {
                    return self::errorResponse("Order qty cannot be greater than PO quantity.", [
                        'order_qty' => $geOrderQty
                    ]);
                }
                if ($availableQty < $inputDiff) {
                    return self::errorResponse("Only {$availableQty} qty can be added. {$poDetail->ge_qty} already used; PO qty is {$poDetail->order_qty}.", [
                        'order_qty' => $geOrderQty
                    ]);
                }
            }
        }

        // === Case 2: Create (GE / ASN / Direct PO) ===
        else {
            // Step 1: Identify PO detail by reference type
            $poDetail = match ($type) {
                ConstantHelper::JO_SERVICE_ALIAS => JoProduct::find($inputData['jo_detail_id']),
                ConstantHelper::PO_SERVICE_ALIAS => PoItem::find($inputData['po_detail_id']),
                ConstantHelper::SO_SERVICE_ALIAS => ErpSoJobWorkItem::find($inputData['so_detail_id']),
                default => null
            };

            $geValidated = false;
            $asnValidated = false;

            // Step 2: Gate Entry validation (if applicable)
            if (!empty($inputData['ge_detail_id'])) {
                $geDetail = GateEntryDetail::find($inputData['ge_detail_id']);
                $balanceQty = floatval($geDetail->accepted_qty - ($geDetail->mrn_qty ?? 0.00));

                if ($balanceQty < $inputQty) {
                    return self::errorResponse("Order qty cannot be greater than Gate Entry qty.", [
                        'order_qty' => number_format((float)$geDetail->accepted_qty, 2)
                    ]);
                }

                $geValidated = true;
            }

            // Step 3: ASN validation (if applicable)
            elseif (!empty($inputData['asn_detail_id'])) {
                $asnDetail = VendorAsnItem::find($inputData['asn_detail_id']);
                $balanceQty = floatval($asnDetail->supplied_qty - ($asnDetail->ge_qty ?? 0.00));

                if ($balanceQty < $inputQty) {
                    return self::errorResponse("Order qty cannot be greater than ASN qty.", [
                        'order_qty' => number_format((float)$asnDetail->supplied_qty, 2)
                    ]);
                }

                $asnValidated = true;
            }

            // Step 5: Tolerance check (if tolerance configured)
            if ($poDetail) {
                $grnQty = floatval($poDetail->ge_qty ?? 0);
                $orderQty = floatval($poDetail->order_qty ?? 0);
                $totalQty = $inputQty + $grnQty;

                // $positiveTol = floatval($item->po_positive_tolerance ?? 0);
                // $negativeTol = floatval($item->po_negative_tolerance ?? 0);

                // $maxAllowed = $orderQty + $positiveTol;
                // $minAllowed = max(0, $orderQty - $negativeTol);

                // if ($positiveTol > 0 || $negativeTol > 0) {
                //     if ($totalQty > $maxAllowed) {
                //         return self::errorResponse("Order qty exceeds allowed positive tolerance.", [
                //             'order_qty' => number_format($orderQty, 2)
                //         ]);
                //     }

                //     if ($totalQty < $minAllowed) {
                //         return self::errorResponse("Order qty is below allowed negative tolerance.", [
                //             'order_qty' => number_format($orderQty, 2)
                //         ]);
                //     }
                // }
                if ($totalQty > $orderQty) {
                    return self::errorResponse("Order qty cannot be greater than po qty.", [
                        'order_qty' => number_format($orderQty, 2)
                    ]);
                }
            }
        }

        // === All Good ===
        return self::successResponse("Quantity Validated", [
            'order_qty' => $inputQty
        ]);
    }

    # Handle GE calculation update from mrn
    public static function updateCalculation($ge)
    {
        $mrn = GateEntryHeader::with(['items.itemDiscount', 'expenses', 'shippingAddress'])->find($ge->header_id);
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
            $existingTaxIds = GateEntryTed::where('detail_id', $item->id)
                ->where('ted_type', 'Tax')
                ->pluck('ted_id')
                ->map('strval')
                ->toArray();

            sort($currentTaxIds);
            sort($existingTaxIds);

            if ($currentTaxIds !== $existingTaxIds) {
                GateEntryTed::where('detail_id', $item->id)
                    ->where('ted_type', 'Tax')
                    ->delete();
            }

            $itemTax = 0;
            foreach ($taxDetails as $tax) {
                $taxAmount = ((float) $tax['tax_percentage'] / 100) * $priceAfterDiscounts;
                $itemTax += $taxAmount;

                GateEntryTed::updateOrCreate(
                    [
                        'detail_id' => $item->id,
                        'ted_id' => $tax['id'],
                        'ted_type' => 'Tax',
                    ],
                    [
                        'header_id' => $mrn->id,
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
        return self::successResponse("Quantity Validated", [
            'order_qty' => $inputQty
        ]);
    }

    private static function errorResponse($message, $inputQty)
    {
        return [
            "code" => "500",
            "status" => "error",
            "order_qty" => $inputQty,
            "message" => $message,
        ];

    }

    private static function successResponse($response, $inputQty)
    {
        return [
            "code" => "200",
            "status" => "success",
            "order_qty" => $inputQty,
            "message" => $response,
        ];
    }
}
