<?php 
namespace App\Services;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

use App\Models\InspectionTed;
use App\Models\InspChecklist;
use App\Models\InspectionHeader;
use App\Models\InspectionDetail;
use App\Models\InspBatchDetail;
use App\Models\InspectionItemAttribute;

use App\Models\InspectionTedHistory;
use App\Models\InspBatchDetailHistory;
use App\Models\InspectionHeaderHistory;
use App\Models\InspectionDetailHistory;
use App\Models\InspectionItemLocation;
use App\Models\InspectionItemAttributeHistory;

use App\Models\Item;
use App\Models\MrnBatchDetail;


use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class InspectionService
{
    public static function insertBatchDetails(array $batchDetails, $inspection, $inspectionDetail)
    {
        try {
            if (empty($batchDetails)) {
                return self::errorResponse('Batch details are required.');
            }

            $now = now();

            // convert to base UOM safely
            $toBase = static function (float $qty) use ($inspectionDetail): float {
                $v = ItemHelper::convertToBaseUom(
                    $inspectionDetail->item_id,
                    $inspectionDetail->uom_id,
                    $qty
                );
                return (float) ($v ?? 0.0);
            };

            // --------- Build rows + aggregate by MRN batch id ----------
            $rows = [];               // for InspBatchDetail::insert()
            $byMrn = [];              // [mrn_id => ['insp' => x, 'insp_inv' => y, 'batch_number' => '...']]

            foreach ($batchDetails as $val) {
                $mrnId   = (int) Arr::get($val, 'mrn_batch_detail_id');
                $bn      = (string) Arr::get($val, 'batch_number', '');
                $mfgYear = Arr::get($val, 'manufacturing_year');
                $expRaw  = Arr::get($val, 'expiry_date');
                $expDate = $expRaw ? Carbon::parse($expRaw)->format('Y-m-d') : null;

                $mrnQty  = (float) Arr::get($val, 'mrn_qty', 0);
                $inspQty = (float) Arr::get($val, 'inspection_qty', 0);
                $accQty  = (float) Arr::get($val, 'accepted_qty', 0);
                $rejQty  = (float) Arr::get($val, 'rejected_qty', 0);

                // aggregate for later MRN updates (and balance check)
                if (!isset($byMrn[$mrnId])) {
                    $byMrn[$mrnId] = ['insp' => 0.0, 'insp_inv' => 0.0, 'batch_number' => $bn];
                }
                $byMrn[$mrnId]['insp']     += $inspQty;
                $byMrn[$mrnId]['insp_inv'] += $toBase($inspQty);

                $rows[] = [
                    'header_id'               => $inspection->id,
                    'detail_id'               => $inspectionDetail->id,
                    'batch_detail_id'         => $mrnId,
                    'item_id'                 => $inspectionDetail->item_id,
                    'batch_number'            => $bn ?: null,
                    'manufacturing_year'      => $mfgYear ?: null,
                    'expiry_date'             => $expDate,                         // null if empty
                    'quantity'                => $mrnQty,
                    'inspection_qty'          => $inspQty,
                    'accepted_qty'            => $accQty,
                    'rejected_qty'            => $rejQty,
                    'inventory_uom_qty'       => $toBase($mrnQty),
                    'inspection_inv_uom_qty'  => $toBase($inspQty),
                    'accepted_inv_uom_qty'    => $toBase($accQty),
                    'rejected_inv_uom_qty'    => $toBase($rejQty),
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }

            // --------- Validate balances BEFORE writing ----------
            $mrnIds = array_keys($byMrn);
            if (!empty($mrnIds)) {
                $mrnRows = MrnBatchDetail::whereIn('id', $mrnIds)
                    ->get()
                    ->keyBy('id');

                foreach ($byMrn as $id => $agg) {
                    $mrn = $mrnRows->get($id);
                    if (!$mrn) {
                        return self::errorResponse("Invalid MRN batch reference (id: {$id}).");
                    }
                    $balance = (float) $mrn->quantity - (float) ($mrn->inspection_qty ?? 0.0);
                    if ($agg['insp'] > $balance + 1e-6) {
                        $bn = $agg['batch_number'] ?: $mrn->batch_number;
                        return self::errorResponse("Batch qty cannot be greater than balance qty for batch [{$bn}].");
                    }
                }
            }

            // --------- Insert inspection batch rows (single query) ----------
            InspBatchDetail::insert($rows);

            // --------- Update MRN batches with conditional atomic increments ----------
            // (Not transactional; we still protect against obvious races by requiring enough balance at update time)
            foreach ($byMrn as $id => $agg) {
                $affected = MrnBatchDetail::where('id', $id)
                    ->whereRaw('(quantity - IFNULL(inspection_qty,0)) >= ?', [$agg['insp']])
                    ->update([
                        'inspection_qty'         => DB::raw('IFNULL(inspection_qty,0) + ' . (float) $agg['insp']),
                        'inspection_inv_uom_qty' => DB::raw('IFNULL(inspection_inv_uom_qty,0) + ' . (float) $agg['insp_inv']),
                        'updated_at'             => $now,
                    ]);

                // If 0 rows affected here, there was a race; we can only report it (no rollback by request)
                if ($affected === 0) {
                    // You can decide to return a warning instead of error if you prefer
                    return self::errorResponse(
                        "Another update consumed the remaining balance for a batch. Please refresh and try again."
                    );
                }
            }

            return self::successResponse("Batch details successfully saved.");

        } catch (\Throwable $e) {
            // No rollback (per your request)
            return self::errorResponse($e->getMessage() . ' on line ' . $e->getLine());
        }
    }

    public static function insertChecklistData(array $itemChecklists, $inspection, $inspectionDetail)
    {
        try {
            if (empty($itemChecklists)) {
                return self::successResponse("No item checklists to save.");
            }

            $now  = now();
            $rows = [];

            foreach ($itemChecklists as $val) {
                $rows[] = [
                    'header_id'          => $inspection->id,
                    'detail_id'          => $inspectionDetail->id,
                    'item_id'            => $inspectionDetail->item_id,
                    'checklist_id'       => Arr::get($val, 'checkList_id'),
                    'checklist_name'     => Arr::get($val, 'checkList_name'),
                    'checklist_detail_id'=> Arr::get($val, 'detail_id'),
                    'name'               => Arr::get($val, 'parameter_name'),
                    'value'              => Arr::get($val, 'parameter_value'),
                    'result'             => Arr::get($val, 'result'),
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            InspChecklist::insert($rows);

            return self::successResponse("Item checklists successfully saved.");
        } catch (\Throwable $e) {
            return self::errorResponse($e->getMessage() . ' on line ' . $e->getLine());
        }
    }

    private static function errorResponse(string $message): array
    {
        return [
            'status'  => 'error',
            'code'    => 500,
            'message' => $message,
            'data'    => null,
        ];
    }

    private static function successResponse(string $message, $data = null): array
    {
        return [
            'status'  => 'success',
            'code'    => 200,
            'message' => $message,
            'data'    => $data,
        ];
    }

    
}