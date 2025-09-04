<?php  
namespace App\Services\Inspection;

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
    // Insert Batch Details
    public static function manageBatchDetails(array $batchDetails, $inspection, $inspectionDetail)
    {
        try {
            if (empty($batchDetails)) {
                return self::errorResponse('Batch details are required.');
            }

            $now = now();

            // ---------- helpers ----------
            $toBase = static function (float $qty) use ($inspectionDetail): float {
                $v = ItemHelper::convertToBaseUom(
                    $inspectionDetail->item_id,
                    $inspectionDetail->uom_id,
                    $qty
                );
                return (float) ($v ?? 0.0);
            };

            // ---------- normalize input, gather ids ----------
            $createRows = [];
            $updateRows = [];
            $updateIds  = [];
            $mrnIds     = [];

            // First pass: normalize values, keep raw rows for later
            $norm = [];
            foreach ($batchDetails as $val) {
                $inspId = Arr::get($val, 'id');
                $mrnId  = (int) Arr::get($val, 'mrn_batch_detail_id');
                $bn     = trim((string) Arr::get($val, 'batch_number', ''));

                $mfg    = Arr::get($val, 'manufacturing_year');
                $expRaw = Arr::get($val, 'expiry_date');
                $exp    = $expRaw ? Carbon::parse($expRaw)->format('Y-m-d') : null;

                $rec    = (float) Arr::get($val, 'mrn_qty', 0);
                $insp   = Arr::get($val, 'inspection_qty', null);
                $acc    = Arr::get($val, 'accepted_qty',   null);
                $rej    = Arr::get($val, 'rejected_qty',   null);

                // Basic required checks
                if ($mrnId <= 0 || $bn === '' || $rec <= 0) {
                    return self::errorResponse('Batch number and receipt qty are required for every batch.');
                }
                if ($insp === null || $insp < 0 || $insp > $rec) {
                    return self::errorResponse("Inspection qty must be between 0 and receipt qty for batch [{$bn}].");
                }
                if ($acc === null || $acc < 0 || $acc > $insp) {
                    return self::errorResponse("Accepted qty must be between 0 and inspection qty for batch [{$bn}].");
                }

                // Derive / validate rejected
                $rej = ($rej === null) ? ($insp - $acc) : (float) $rej;
                if (abs(($insp - $acc) - $rej) > 1e-9) {
                    return self::errorResponse("Rejected qty must equal (Inspection − Accepted) for batch [{$bn}].");
                }

                $norm[] = [
                    'insp_id'     => $inspId ? (int)$inspId : null,
                    'mrn_id'      => $mrnId,
                    'batch_no'    => $bn,
                    'mfg'         => $mfg ?: null,
                    'exp'         => $exp,
                    'rec'         => (float)$rec,
                    'insp'        => (float)$insp,
                    'acc'         => (float)$acc,
                    'rej'         => (float)$rej,
                ];

                if ($inspId) $updateIds[] = (int)$inspId;
                $mrnIds[] = $mrnId;
            }

            // ---------- fetch existing insp rows for updates ----------
            $existingById = collect();
            if (!empty($updateIds)) {
                $existingById = InspBatchDetail::whereIn('id', $updateIds)->get()->keyBy('id');
            }

            // ---------- fetch MRN rows and init remaining balance ----------
            $mrnRows = MrnBatchDetail::whereIn('id', $mrnIds)->get()->keyBy('id');
            $remainingByMrn = [];
            foreach ($mrnRows as $mrn) {
                $remainingByMrn[$mrn->id] = max(0.0, (float)$mrn->quantity - (float)($mrn->inspection_qty ?? 0.0));
            }

            // Will accumulate deltas to update MRN at the end
            $deltaByMrn = []; // [mrnId => ['insp' => Δinsp, 'insp_inv' => ΔinspInv, 'bn' => lastBatchNo]]

            // ---------- per-row balance check against running remaining ----------
            foreach ($norm as $row) {
                $mrnId = $row['mrn_id'];
                $bn    = $row['batch_no'];

                $mrn = $mrnRows->get($mrnId);
                if (!$mrn) {
                    return self::errorResponse("Invalid MRN batch reference (id: {$mrnId}).");
                }

                // Old insp qty for this insp row (0 if insert)
                $oldInsp    = 0.0;
                $oldInspInv = 0.0;
                if ($row['insp_id']) {
                    $ex = $existingById->get($row['insp_id']);
                    if (!$ex) {
                        return self::errorResponse("Invalid inspection-batch id [{$row['insp_id']}].");
                    }
                    if ((int)$ex->batch_detail_id !== $mrnId) {
                        return self::errorResponse("Batch mapping cannot be changed for batch [{$bn}].");
                    }
                    $oldInsp    = (float)$ex->inspection_qty;
                    $oldInspInv = (float)$ex->inspection_inv_uom_qty;
                }

                $deltaInsp    = $row['insp'] - $oldInsp;
                $deltaInspInv = $toBase($row['insp']) - $oldInspInv;

                if ($deltaInsp > 0) {
                    $remaining = $remainingByMrn[$mrnId] ?? 0.0;
                    // Your exact condition: (balance) - (incoming − old) >= 0
                    if ($deltaInsp > $remaining + 1e-9) {
                        $bnShow = $bn ?: $mrn->batch_number;
                        return self::errorResponse("Batch qty cannot exceed balance for batch [{$bnShow}].");
                    }
                    // consume remaining for this MRN
                    $remainingByMrn[$mrnId] = $remaining - $deltaInsp;
                }

                // accumulate MRN deltas to apply later
                if (!isset($deltaByMrn[$mrnId])) {
                    $deltaByMrn[$mrnId] = ['insp' => 0.0, 'insp_inv' => 0.0, 'bn' => $bn];
                }
                $deltaByMrn[$mrnId]['insp']    += $deltaInsp;
                $deltaByMrn[$mrnId]['insp_inv']+= $deltaInspInv;

                // build row payload for insert/upsert
                $payload = [
                    'header_id'              => $inspection->id,
                    'detail_id'              => $inspectionDetail->id,
                    'batch_detail_id'        => $mrnId,
                    'item_id'                => $inspectionDetail->item_id,
                    'batch_number'           => $row['batch_no'],
                    'manufacturing_year'     => $row['mfg'],
                    'expiry_date'            => $row['exp'],
                    'quantity'               => $row['rec'],
                    'inspection_qty'         => $row['insp'],
                    'accepted_qty'           => $row['acc'],
                    'rejected_qty'           => $row['rej'],
                    'inventory_uom_qty'      => $toBase($row['rec']),
                    'inspection_inv_uom_qty' => $toBase($row['insp']),
                    'accepted_inv_uom_qty'   => $toBase($row['acc']),
                    'rejected_inv_uom_qty'   => $toBase($row['rej']),
                    'updated_at'             => $now,
                ];

                if ($row['insp_id']) {
                    $payload['id'] = $row['insp_id'];
                    $updateRows[]  = $payload;
                } else {
                    $payload['created_at'] = $now;
                    $createRows[] = $payload;
                }
            }

            // ---------- write rows ----------
            if (!empty($createRows)) {
                InspBatchDetail::insert($createRows);
            }
            if (!empty($updateRows)) {
                InspBatchDetail::upsert(
                    $updateRows,
                    ['id'],
                    [
                        'batch_number','manufacturing_year','expiry_date',
                        'quantity','inspection_qty','accepted_qty','rejected_qty',
                        'inventory_uom_qty','inspection_inv_uom_qty',
                        'accepted_inv_uom_qty','rejected_inv_uom_qty',
                        'updated_at'
                    ]
                );
            }

            // ---------- reflect deltas into MRN (atomic math; no transaction) ----------
            foreach ($deltaByMrn as $mrnId => $d) {
                $Δinsp    = (float) $d['insp'];      // may be negative
                $ΔinspInv = (float) $d['insp_inv'];

                MrnBatchDetail::where('id', $mrnId)->update([
                    'inspection_qty'         => DB::raw('GREATEST(0, IFNULL(inspection_qty,0) + ' . $Δinsp    . ')'),
                    'inspection_inv_uom_qty' => DB::raw('GREATEST(0, IFNULL(inspection_inv_uom_qty,0) + ' . $ΔinspInv . ')'),
                    'updated_at'             => $now,
                ]);
            }

            return self::successResponse('Batch details successfully saved.');
        } catch (\Throwable $e) {
            return self::errorResponse($e->getMessage() . ' on line ' . $e->getLine());
        }
    }



    // Manage Checklist Data
    public static function manageChecklistData(array $itemChecklists, $inspection, $inspectionDetail)
    {
        try {
            if (empty($itemChecklists)) {
                return self::successResponse('No item checklists to save.');
            }

            $now         = now();
            $insertRows  = [];
            $updateRows  = [];

            foreach ($itemChecklists as $val) {
                // normalize inputs
                $row = [
                    'header_id'           => $inspection->id,
                    'detail_id'           => $inspectionDetail->id,
                    'item_id'             => $inspectionDetail->item_id,
                    'checklist_id'        => Arr::get($val, 'checkList_id'),
                    'checklist_name'      => Arr::get($val, 'checkList_name'),
                    'checklist_detail_id' => Arr::get($val, 'detail_id'),
                    'name'                => Arr::get($val, 'parameter_name'),
                    'value'               => Arr::get($val, 'parameter_value'),
                    'result'              => Arr::get($val, 'result'),
                ];

                $id = Arr::get($val, 'insp_checklist_id');

                if ($id) {
                    // UPDATE path
                    $updateRows[] = array_merge($row, [
                        'id'         => (int) $id,
                        'updated_at' => $now,
                    ]);
                } else {
                    // INSERT path
                    $insertRows[] = array_merge($row, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // Bulk insert new items
            if (!empty($insertRows)) {
                InspChecklist::insert($insertRows);
            }

            // Bulk update existing items (atomic upsert on primary key id)
            if (!empty($updateRows)) {
                // Uses Laravel's query builder upsert (MySQL/MariaDB/Postgres supported)
                DB::table('erp_insp_checklists')->upsert(
                    $updateRows,
                    ['id'], // unique-by (primary key)
                    [
                        'header_id',
                        'detail_id',
                        'item_id',
                        'checklist_id',
                        'checklist_name',
                        'checklist_detail_id',
                        'name',
                        'value',
                        'result',
                        'updated_at',
                    ]
                );
                // If your DB version doesn’t support upsert, fall back to per-row updates:
                // foreach ($updateRows as $r) {
                //     $id = $r['id']; unset($r['id']);
                //     DB::table('erp_insp_checklists')->where('id', $id)->update($r);
                // }
            }

            return self::successResponse('Item checklists successfully saved.');
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