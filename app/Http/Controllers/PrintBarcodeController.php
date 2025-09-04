<?php

namespace App\Http\Controllers;

use DB;
use Str;
use PDF;
use Auth;
use View;
use Session;
use stdClass;
use DateTime;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnAttribute;
use App\Models\MrnBatchDetail;

use App\Models\InspectionHeader;

use App\Models\WHM\ErpWhmJob;
use App\Models\WHM\ErpItemUniqueCode;

use App\Models\Employee;
use App\Models\ErpVendor;
use App\Models\WhStructure;
use App\Models\WhItemMapping;
use App\Models\ErpFinancialYear;

use App\Models\Book;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\ErpStore;
use App\Models\Organization;

use App\Models\StockLedger;
use App\Models\StockLedgerItemAttribute;

use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\MrnModuleHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\StoragePointHelper;

use App\Services\MrnService;
use App\Lib\Services\WHM\WhmJob;
use App\Services\MrnDeleteService;
use App\Services\MrnCheckAndUpdateService;
use App\Services\TransactionCalculationService;

use App\Services\CommonService;
use Illuminate\Support\Facades\Validator;
use P360\ClientConfig\Services\ClientConfigService;

class PrintBarcodeController extends Controller
{
    protected $mrnService;

    protected $organization_id;
    protected $group_id;
    protected $moduleType;


    public function __construct(MrnService $mrnService)
    {
        $this->mrnService = $mrnService;
    }

    // Page shell (table + JS). Data is loaded via AJAX.
    public function page(Request $request, int $id)
    {
        // Access check
        $parentAlias = $request->segment(1);
        if (!Helper::getAccessibleServicesFromMenuAlias($parentAlias)) {
            // Render page but JS will show Swal and stop DT init
            return view('partials.get-barcodes', [
                'header' => null,
                'module_type' => $request->get('module_type'),
                'reference_id' => $request->reference_id,
                'bootstrapError' => 'You do not have access to this service.',
            ]);
        }

        // Validate module_type
        $v = Validator::make($request->all(), [
            'module_type' => 'required',
        ], [
            'module_type.required' => 'Please send module_type.',
        ]);

        if ($v->fails()) {
            return view('partials.get-barcodes', [
                'header' => null,
                'module_type' => $request->get('module_type'),
                'reference_id' => $request->reference_id,
                'bootstrapError' => $v->errors()->first(),
            ]);
        }

        $documentHeaderId = $id;

        if ($request->reference_id) {
            $inspHeader = InspectionHeader::select('id')->find($request->reference_id);
            if (!$inspHeader) {
                return response()->json(['error' => 'Inspection not found.'], 404);
            }
            // Single lookup for job
            $whmJob = ErpWhmJob::withDefaultGroupCompanyOrg()
                ->select('id', 'morphable_id', 'reference_id')
                ->where('reference_id', $inspHeader->id)
                ->first();

            if (!$whmJob) {
                return view('partials.get-barcodes', [
                    'header' => null,
                    'module_type' => $request->get('module_type'),
                    'reference_id' => $request->reference_id,
                    'bootstrapError' => 'Job not found for inspection.',
                ]);
            }
        }

        $refData = self::getModuleWiseData($request->module_type, $documentHeaderId);

        // Single lookup for job
        $whmJob = ErpWhmJob::withDefaultGroupCompanyOrg()
            ->select('id', 'morphable_id', 'reference_id')
            ->where('morphable_type', $refData['morphable_type'])
            ->where('morphable_id', $refData['morphable_id'])
            ->first();

        if (!$whmJob) {
            return view('partials.get-barcodes', [
                'header' => null,
                'module_type' => $request->get('module_type'),
                'reference_id' => $request->reference_id,
                'bootstrapError' => 'Job not found.',
            ]);
        }

        return view('partials.get-barcodes', [
            'header' => $whmJob,
            'bootstrapError' => null,
            'module_type' => $request->module_type,
            'reference_id' => $request->reference_id,
        ]);
    }

    // DataTables server-side JSON
    public function data(Request $request, int $id)
    {
        // ---------- Access ----------
        $parentAlias = $request->segment(1);
        if (!Helper::getAccessibleServicesFromMenuAlias($parentAlias)) {
            return response()->json(['error' => 'You do not have access to this service.'], 403);
        }

        // ---------- Validate ----------
        $v = Validator::make($request->all(), [
            'module_type' => 'required',
            'draw' => 'required|integer',
            'start' => 'nullable|integer',
            'length' => 'nullable|integer',
            'search.value' => 'nullable|string',
        ], [
            'module_type.required' => 'Please send module_type.',
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()->first()], 422);
        }

        // ---------- Resolve context ----------
        $documentHeaderId = $id;
        $referenceDetailIds = [];

        if ($request->reference_id) {
            $inspHeader = InspectionHeader::select('id')->find($request->reference_id);
            if (!$inspHeader) {
                return response()->json(['error' => 'Inspection not found.'], 404);
            }
            $referenceDetailIds = $inspHeader->items()->pluck('id')->all(); // array

            // Single lookup for job
            $whmJob = ErpWhmJob::withDefaultGroupCompanyOrg()
                ->select('id', 'morphable_id', 'reference_id')
                ->where('reference_id', $inspHeader->id)
                ->first();

            if (!$whmJob) {
                return response()->json(['error' => 'Record not found  for inspection.'], 404);
            }
        }

        $refData = self::getModuleWiseData($request->module_type, $documentHeaderId);

        // Single lookup for job
        $whmJob = ErpWhmJob::withDefaultGroupCompanyOrg()
            ->select('id', 'morphable_id', 'reference_id')
            ->where('morphable_type', $refData['morphable_type'])
            ->where('morphable_id', $refData['morphable_id'])
            ->first();

        if (!$whmJob) {
            return response()->json(['error' => 'Job not found.'], 404);
        }

        // Normalize request reference_id(s) -> array of ints/strings you resolved to detail IDs
        // $referenceDetailIds should already contain the valid detail IDs corresponding to request()->reference_id
        // e.g. $referenceDetailIds = [...]; // [] when invalid/nonexistent

        $filterByRef = $request->filled('reference_id'); // user sent reference_id?

        $base = ErpItemUniqueCode::query()
            ->where('job_id', $whmJob->id)
            ->where('trns_type', $request->module_type)
            ->when(
                $filterByRef,
                // user sent reference_id -> keep only those refs; if none valid -> no rows
                function ($q) use ($referenceDetailIds) {
                    if (empty($referenceDetailIds)) {
                        $q->whereRaw('1=0'); // reference_id sent but not found => show nothing
                    } else {
                        $q->whereIn('reference_detail_id', $referenceDetailIds);
                    }
                },
                // user did NOT send reference_id -> exclude all rows that have any reference_detail_id
                function ($q) {
                    $q->whereNull('reference_detail_id');
                }
            )
            ->with([
                'vendor:id,company_name',
                'store:id,store_name',
                'subStore:id,name',
            ])
            ->select([
                'id',
                'uid',
                'item_uid',
                'vendor_id',
                'item_name',
                'item_code',
                'item_attributes',
                'batch_number',
                'serial_no',
                'store_id',
                'sub_store_id',
                'reference_detail_id',
            ]);


        // For DataTables:
        // recordsTotal = count before search
        $recordsTotal = (clone $base)->count();

        // ---------- Apply global search ----------
        $search = (string) $request->input('search.value', '');
        $filtered = (clone $base);
        if ($search !== '') {
            $filtered->where(function ($w) use ($search) {
                $w->where('uid', 'like', "%{$search}%")
                    ->orWhere('item_uid', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhere('serial_no', 'like', "%{$search}%");
            });
        }

        // recordsFiltered = count after search
        $recordsFiltered = (clone $filtered)->count();

        // ---------- Ordering (whitelist) ----------
        $orderColIdx = (int) data_get($request->input('order'), '0.column', 1);
        $orderDirRaw = (string) data_get($request->input('order'), '0.dir', 'asc');
        $orderDir = $orderDirRaw === 'desc' ? 'desc' : 'asc';

        // map DT column index -> DB column
        $orderableMap = [
            1 => 'uid',
            2 => 'vendor_id',    // sorting by vendor name would need a join/sortBy; keep id for perf
            3 => 'item_name',
            4 => 'item_code',
            5 => 'item_attributes', // optional; JSON sort may be SQL-dependent
            6 => 'batch_number',
            7 => 'store_id',
            8 => 'sub_store_id',
        ];
        $orderCol = $orderableMap[$orderColIdx] ?? 'id';
        $filtered->orderBy($orderCol, $orderDir);

        // ---------- Paging ----------
        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 25);
        if ($length < 1)
            $length = 25;
        if ($length > 500)
            $length = 500; // safety cap

        $rows = $filtered->skip($start)->take($length)->get();

        // ---------- Transform ----------
        $data = $rows->map(function ($r) {
            $attrs = $r->item_attributes;
            if (is_string($attrs)) {
                $attrs = json_decode($attrs, true) ?: [];
            } elseif (!is_array($attrs)) {
                $attrs = [];
            }
            $attrStr = collect($attrs)->map(function ($a) {
                $name = $a['attribute_name'] ?? '';
                $value = $a['attribute_value'] ?? '';
                return trim($name . $value) === '' ? null : "{$name} : {$value}";
            })->filter()->implode(', ');

            return [
                'select' => '<input type="checkbox" class="form-check-input row-check" value="' . e($r->item_uid) . '">',
                'uid' => e($r->uid),
                'vendor' => e(optional($r->vendor)->company_name),
                'item_name' => e($r->item_name),
                'item_code' => e($r->item_code),
                'attributes' => e($attrStr ?: '—'),
                'batch_no' => e($r->batch_number),
                'store' => e(optional($r->store)->store_name),
                'sub_store' => e(optional($r->subStore)->name),
                'serial_no' => e($r->serial_no),
                'item_uid' => e($r->item_uid),
                'qr' => $r->item_uid
                    ? '<img alt="' . e($r->item_uid) . '" style="height:60px;width:60px;" src="data:image/png;base64,' . \DNS2D::getBarcodePNG($r->item_uid, 'QRCODE') . '" />'
                    : '',
                'row_id' => $r->uid,
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }


    # Print Labels
    public function print(string $module_type, int $id, Request $request)
    {
        // Single lookup for job
        $whmJob = ErpWhmJob::withDefaultGroupCompanyOrg()
            ->find($id);

        if (!$whmJob) {
            return response()->json(['error' => 'Job not found.'], 404);
        }

        $ids = $request->input('ids', []);
        if (!is_array($ids)) { // handle accidental comma-separated string
            $ids = array_filter(array_map('trim', explode(',', (string) $ids)));
        }

        // Base query (unique codes table) with relations
        $barCodes = ErpItemUniqueCode::query()
            ->where('job_id', $whmJob->id)
            ->whereIn('item_uid', $ids)
            ->where('trns_type', $module_type)
            ->with([
                'vendor:id,company_name',
                'store:id,store_name',
                'subStore:id,name',
            ])
            ->select([
                'id',
                'item_uid',
                'vendor_id',
                'item_name',
                'item_code',
                'item_attributes',
                'batch_number',
                'serial_no',
                'store_id',
                'sub_store_id'
            ])
            ->get();

        $html = view('partials.print-barcodes', compact('barCodes', 'whmJob'))->render();

        return response()->json([
            'status' => 200,
            'html' => $html
        ]);
    }

    # Get Module Wise Data
    private static function getModuleWiseData(string $moduleType, int $id): array
    {
        // Map module types to models
        $map = [
            'grn' => MrnHeader::class,
            'inspection' => InspectionHeader::class,
        ];

        // Pick model, defaulting to MrnHeader
        $morphableType = $map[$moduleType] ?? MrnHeader::class;

        return [
            'morphable_type' => $morphableType,
            'morphable_id' => $id,
        ];
    }
}
