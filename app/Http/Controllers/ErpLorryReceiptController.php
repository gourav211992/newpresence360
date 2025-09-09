<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiGenericException;
use App\Helpers\BookHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\ErpRouteMaster;
use App\Http\Requests\LorryReceiptRequest;
use App\Models\ErpLogisticsMultiFixedPricing;
use App\Models\ErpLogisticsMultiFixedLocation;
use App\Models\ErpLogisticsMultiPointPricing;
use App\Models\ErpVehicle;
use App\Models\ErpLogisticLRMedia;
use App\Models\ErpLorryReceiptHistory;
use App\Models\ErpLorryReceipt;
use App\Models\ErpLogisticsLrLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\AuthUser;
use Illuminate\Support\Facades\Crypt;
use App\Jobs\SendEmailJob;
use Yajra\DataTables\DataTables;
use App\Helpers\InventoryHelper;
use App\Models\CostCenterOrgLocations;
use App\Models\Customer;
use App\Models\ErpDriver;
use App\Models\Organization;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Mail\LorryReceiptMail;
use Illuminate\Support\Facades\Mail;
use Exception;
use Auth;
use Carbon\Carbon;
use PDF;
use stdClass;

class ErpLorryReceiptController extends Controller
{
   public function index(Request $request)
{
   
    $user = Helper::getAuthenticatedUser();
    
    $organization = Organization::find($user->organization_id);

    $drivers = ErpDriver::where('organization_id', $organization->id)->where('status', 'active')->get();
    $vehicles = ErpVehicle::withDefaultGroupCompanyOrg()->where('status', 'active')->get();
   

    if ($request->ajax()) {
        $lrs = ErpLorryReceipt::with([
                'source', 
                'destination', 
                'driver', 
                'vehicle', 
                'consignor', 
                'consignee', 
                'auth_user'
            ])
            ->withDefaultGroupCompanyOrg()
            ->withDraftListingLogic() 
            ->orderByDesc('id');

        if ($request->filled('lr_no')) {
            $lrs->where('document_number', 'like', '%' . $request->lr_no . '%');
        }

        if ($request->filled('source_id')) {
            $lrs->where('origin_id', $request->source_id);
        }

        if ($request->filled('destination_id')) {
            $lrs->where('destination_id', $request->destination_id);
        }

        if ($request->filled('driver_id')) {
            $lrs->where('driver_id', $request->driver_id);
        }

        if ($request->filled('status')) {
            $lrs->where('status', $request->status);
        }

        if ($request->filled('document_date')) {
            $lrs->whereDate('document_date', $request->document_date);
        }

        return DataTables::of($lrs)
            ->addIndexColumn()
            ->editColumn('document_date', function ($row) {
                return $row->getFormattedDate('document_date') ?? 'N/A';
            })

            ->addColumn('source_name', fn($row) => $row->source->name ?? '-')
            ->addColumn('destination_name', fn($row) => $row->destination->name ?? '-')
            ->addColumn('driver_name', fn($row) => $row->driver->name ?? '-')
            ->addColumn('vehicle_no', fn($row) => $row->vehicle->lorry_no ?? '-')
            ->addColumn('total_charges', fn($row) => $row->total_charges ?? '-')
            ->addColumn('series', fn($row) => $row->book->book_name ?? '-')
            
            ->editColumn('created_by', function ($row) {
                    $createdBy = optional($row->auth_user)->name ?? 'N/A'; 
                    return $createdBy;
                })
           ->addColumn('document_status_html', function ($row) {
                $colors = [
                    'draft' => 'badge-light-warning',
                    'approved' => 'badge-light-success',
                    'rejected' => 'badge-light-danger',
                    'submitted' => 'badge-light-primary',
                    'partially_approved' => 'badge-light-warning',
                    'approval_not_required' => 'badge-light-success', 
                ];
                $badge = $colors[$row->document_status] ?? 'badge-light-secondary';
                $status = $row->document_status === 'approval_not_required' ? 'Approved' : ucfirst($row->document_status);
                return '<span class="badge rounded-pill ' . $badge . '">' . $status . '</span>';
            })

            ->addColumn('action', function ($row) {
                return '
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . route('logistics.lorry-receipt.edit', $row->id) . '">
                                <i data-feather="edit-3" class="me-50"></i>
                                <span>View/Edit Detail</span>
                            </a>
                        </div>
                    </div>';
            })
            ->rawColumns(['document_status', 'action'])
            ->make(true);
    }

    return view('logistics.lorry-receipt.index', compact('drivers', 'vehicles'));
}

    
    public function create(Request $request){

       $segments = request()->segments(); 
       $pathUrl = $segments['0'].'/'.$segments['1'];
       $pathUrl = str_replace('/', '_', $pathUrl);
    
        $redirectUrl = route('logistics.lorry-receipt.create');
        $lorryReceipt = ConstantHelper::LR_SERVICE_ALIAS;
    
        request() -> merge(['type' => $lorryReceipt]);
        $lorryReceipt = $request -> input('type', ConstantHelper::LR_SERVICE_ALIAS);
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($pathUrl);
        
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }

        $series = Helper:: getBookSeriesNew($lorryReceipt, $pathUrl)->get();
        $firstService = $servicesBooks['services'][0];
        $user = Helper::getAuthenticatedUser();
        $bookTypeAlias = ConstantHelper::LR_SERVICE_ALIAS;
        $lorryCharges = ConstantHelper::LORRY_CHARGES;
        $customers = Customer::withDefaultGroupCompanyOrg()->where('status','active')->get();
        $drivers = ErpDriver::withDefaultGroupCompanyOrg()->where('status','active')->get();
        $locations = InventoryHelper::getAccessibleLocations();
        $vehicleNumbers = ErpVehicle::withDefaultGroupCompanyOrg()->where('status','active')->get();
        $routeMasters = ErpRouteMaster::withDefaultGroupCompanyOrg()->where('status','active')->get();
       
     

        return view('logistics.lorry-receipt.create', compact('series', 'routeMasters','customers', 'drivers', 'vehicleNumbers', 'locations','lorryCharges'));
    }


    public function edit(Request $request, $id)
    {
       $users = AuthUser::where('organization_id', Helper::getAuthenticatedUser()->organization_id)
                ->where('status', ConstantHelper::ACTIVE)
                ->select('id', 'name', 'email')
                ->get();
    
        $user = Helper::getAuthenticatedUser();
        $segments = request()->segments(); 
        $pathUrl = $segments[0] . '/' . $segments[1];
        $pathUrl = str_replace('/', '_', $pathUrl);
        $redirectUrl = route('logistics.lorry-receipt.edit', $id); 
        $lorryReceiptType = ConstantHelper::LR_SERVICE_ALIAS;

        $isRevision = $request->has('revisionNumber');
        $revNo = $isRevision ? intval($request->revisionNumber) : null;

        if ($isRevision) {
            $historyLr = ErpLorryReceiptHistory::with([
                'consignor:id,company_name',
                'consignee:id,company_name',
                'driver:id,name',
                'vehicle:id,lorry_no,vehicle_type_id',
                'locations.route:id,name',
                'mediaAttachments'
            ])
            ->where('source_id', $id)
            ->where('revision_number', $revNo)
            ->first();
            $Id = $historyLr->source_id;
    
            if (!$historyLr) {
                $lr = ErpLorryReceipt::with([
                    'consignor:id,company_name',
                    'consignee:id,company_name',
                    'driver:id,name',
                    'vehicle:id,lorry_no,vehicle_type_id',
                    'locations.route:id,name',
                    'mediaAttachments'
                ])->findOrFail($id);
                $historyLr = $lr;
                $Id = $lr->id;
            } else {
                $lr = $historyLr;
                
            }
        } else {
            $lr = ErpLorryReceipt::with([
                'consignor:id,company_name,email',
                'consignee:id,company_name,email',
                'driver:id,name',
                'vehicle:id,lorry_no,vehicle_type_id',
                'locations.route:id,name',
                'mediaAttachments'
            ])->where('id', $id)->withDefaultGroupCompanyOrg()->firstOrFail();
            $historyLr = $lr;
            $revNo = $lr->revision_number;
            $Id = $lr->id;
        }

        if (!$lr) {
            return redirect()->route('logistics.lorry-receipt.index')->with('error', 'Lorry Receipt not found.');
        }

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($pathUrl);

        if (count($servicesBooks['services']) === 0) {
            return redirect()->route('/');
        }

        $series         = Helper::getBookSeriesNew($lorryReceiptType, $pathUrl)->get();
        $firstService   = $servicesBooks['services'][0];
        $lorryCharges   = ConstantHelper::LORRY_CHARGES;
        $customers      = Customer::withDefaultGroupCompanyOrg()->where('status', 'active')->select('id', 'company_name', 'email')->get();
        $drivers        = ErpDriver::withDefaultGroupCompanyOrg()->where('status', 'active')->select('id', 'name')->get();
        $locations      = InventoryHelper::getAccessibleLocations();
        $vehicleNumbers = ErpVehicle::withDefaultGroupCompanyOrg()->where('status','active')->select('id', 'lorry_no', 'vehicle_type_id')->get();
        $userType       = Helper::userCheck();
        $routeMasters   = ErpRouteMaster::withDefaultGroupCompanyOrg()->where('status', 'active')->select('id', 'name')->get();

        $revision_number = $historyLr->revision_number;
        $buttons = Helper::actionButtonDisplay(
            $historyLr->book_id,
            $historyLr->document_status,
            $historyLr->id,
            $historyLr->total_charges,
            $historyLr->approval_level,
            $historyLr->created_by ?? 0,
            $userType['type']
        );

        $approvalHistory = Helper::getApprovalHistory(
            $historyLr->book_id,
            $Id,
            $revNo,
            $historyLr->total_charges,
            $historyLr->created_by ?? 0
        );

        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$lr->document_status] ?? '';
        
        return view('logistics.lorry-receipt.edit', compact(
            'lr',
            'series',
            'routeMasters',
            'approvalHistory',
            'docStatusClass',
            'buttons',
            'customers',
            'drivers',
            'vehicleNumbers',
            'locations',
            'lorryCharges',
            'revision_number',
            'users'
        ));
    }




    public function getCostCentersByLocation($locationId)
    {
        $costCenters = CostCenterOrgLocations::with('costCenter')
            ->where('location_id', $locationId)
            ->get()
            ->pluck('costCenter')
            ->map(function ($center) {
                return [
                    'id' => $center->id,
                    'name' => $center->name,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $costCenters,
        ]);
    }



    public function store(LorryReceiptRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        DB::beginTransaction();
        // dd($request->all());

        try {
            
            $parameters = [];
            $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
            if ($response['status'] === 200) {
                $parameters = json_decode(json_encode($response['data']['parameters']), true);
            }
            // Create LR record
            $lr = new ErpLorryReceipt();
            $lr->organization_id   = $organization->id;
            $lr->group_id          = $organization->group_id;
            $lr->company_id        = $user->company_id ?? null;
            $lr->book_id           = $request->book_id;
            $document_number = $request->document_number ?? null;

             $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
            if (!isset($numberPatternData)) {
                return response()->json([
                    'message' => "Invalid Book",
                    'error' => "",
                ], 422);
            }
            $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $document_number;
            $regeneratedDocExist = ErpLorryReceipt::where('book_id',$request->book_id)
                ->where('document_number',$document_number)->first();
                //Again check regenerated doc no
                if (isset($regeneratedDocExist)) {
                    return response()->json([
                        'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
                        'error' => "",
                    ], 422);
            }

            $lr->doc_number_type = $numberPatternData['type'];
            $lr->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $lr->doc_prefix = $numberPatternData['prefix'];
            $lr->doc_suffix = $numberPatternData['suffix'];
            $lr->doc_no = $numberPatternData['doc_no'];
               
            $lr->document_number = $document_number;
            $lr->document_date     = $request->document_date;
            $lr->location_id       = $request->location;
            $lr->cost_center_id    = $request->cost_center_id;
            $lr->origin_id         = $request->source_id;
            $lr->destination_id    = $request->destination_id;
            $lr->consignor_id      = $request->customer_id;
            $lr->consignee_id      = $request->consignee_id;
            $lr->vehicle_id        = $request->vehicle_number_id;
            $lr->distance          = $request->distance;
            $lr->freight_charges   = $request->freight_charges;
            $lr->driver_id         = $request->driver_id;
            $lr->driver_cash       = $request->driver_cash ?? 0;
            $lr->fuel_price        = $request->fuel_price ?? 0;
            $lr->invoice_no        = $request->invoice_no;
            $lr->invoice_value     = $request->invoice_value ?? 0;
            $lr->no_of_bundles     = $request->no_of_bundles;
            $lr->weight            = $request->weight;
            $lr->ewaybill_no       = $request->ewaybill_no;
            $lr->gst_paid_by       = $request->gst_paid_by;
            $lr->lr_type           = $request->lr_type;
            $lr->billing_type      = $request->billing_type;
            $lr->load_type         = $request->load_type;
            $lr->lr_charges        = $request->lr_charges ?? 0;
            $lr->sub_total         = $request->sub_total ?? 0;
            $lr->total_charges     = $request->total_freight ?? 0;
            $lr->remarks           = $request->remarks;
            $lr->created_by        = $user->auth_user_id ;
            $lr->save();
           
            if ($request->status == ConstantHelper::SUBMITTED) 
            {
                    $bookId = $request->book_id;
                    $docId = $lr->id;
                    $remarks = $lr->remarks;
                    $attachments = $request->file('attachment');
                    $currentLevel = $lr->approval_level ?? 1;
                    $revisionNumber = $lr->revision_number ?? 0;
                    $actionType = 'submit'; // Approve // reject // submit
                    $modelName = get_class($lr);
                    $totalValue = $lr->total_charges ?? 0;
                    
                    $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                    
                    $lr->document_status = $approveDocument['approvalStatus'] ?? $lr->status;
                    $lr->save();
            } 
            // dd($lr);
            $this->handleLorryMediaUploads($request, $lr);


            // Save related locations
            foreach ($request->locations as $location) {
                ErpLogisticsLrLocation::create([
                    'lorry_receipt_id' => $lr->id,
                    'location_id'      => $location['location_id'],
                    'type'             => $location['type'],
                    'no_of_articles'   => $location['no_of_articles'],
                    'weight'           => $location['weight'],
                    'amount'           => $location['freight'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record created successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while saving the Lorry Receipt.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(LorryReceiptRequest $request, $id)
{
   
    $user = Helper::getAuthenticatedUser();
    $organization = $user->organization;

    DB::beginTransaction();

    try {

        $parameters = [];
        $response = BookHelper::fetchBookDocNoAndParameters($request->book_id, $request->document_date);
        if ($response['status'] === 200) {
            $parameters = json_decode(json_encode($response['data']['parameters']), true);
        }
        $lr = ErpLorryReceipt::findOrFail($id);
        $currentStatus = $lr->document_status;
        $actionType = $request->action_type; 
        $amendRemarks = $request->amend_remarks ?? null;

        if (($lr->document_status == ConstantHelper::APPROVED || $lr->document_status == ConstantHelper::APPROVAL_NOT_REQUIRED)
            && $actionType == 'amendment') {

            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'ErpLorryReceipt', 'relation_column' => ''],
                ['model_type' => 'detail', 'model_name' => 'ErpLogisticsLrLocation', 'relation_column' => 'lorry_receipt_id'],
              
                
            ];

            Helper::documentAmendment($revisionData, $id);
        }


        // Update main LR fields
        $lr->document_date     = $request->document_date;
        $lr->location_id       = $request->location;
        $lr->cost_center_id    = $request->cost_center_id;
        $lr->origin_id         = $request->source_id;
        $lr->destination_id    = $request->destination_id;
        $lr->consignor_id      = $request->customer_id;
        $lr->consignee_id      = $request->consignee_id;
        $lr->vehicle_id        = $request->vehicle_number_id;
        $lr->distance          = $request->distance;
        $lr->freight_charges   = $request->freight_charges;
        $lr->driver_id         = $request->driver_id;
        $lr->driver_cash       = $request->driver_cash ?? 0;
        $lr->fuel_price        = $request->fuel_price ?? 0;
        $lr->invoice_no        = $request->invoice_no;
        $lr->invoice_value     = $request->invoice_value ?? 0;
        $lr->no_of_bundles     = $request->no_of_bundles;
        $lr->weight            = $request->weight;
        $lr->ewaybill_no       = $request->ewaybill_no;
        $lr->gst_paid_by       = $request->gst_paid_by;
        $lr->lr_type           = $request->lr_type;
        $lr->billing_type      = $request->billing_type;
        $lr->load_type         = $request->load_type;
        $lr->lr_charges        = $request->lr_charges ?? 0;
        $lr->sub_total         = $request->sub_total ?? 0;
        $lr->total_charges     = $request->total_freight ?? 0;
        $lr->remarks           = $request->remarks;
        $lr->document_status   = $request->document_status ?? ConstantHelper::DRAFT;
        $lr->updated_by        = $user->auth_user_id ;
        $lr->save();

        $bookId = $lr->book_id;
        $docId = $lr->id;
        $remarks = $lr->remarks;
        $amendAttachments = $request->file('amend_attachments');
        $attachments = $request->file('attachment');
        $currentLevel = $lr->approval_level ?? 1;
        $modelName = get_class($lr);
            
     
        if (($currentStatus == ConstantHelper::APPROVED || $currentStatus == ConstantHelper::APPROVAL_NOT_REQUIRED) && $actionType == 'amendment') {
            $revisionNumber =  $lr->revision_number + 1;
            $actionType = 'amendment';
            $totalValue =      $lr->total_charges ?? 0;
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $amendRemarks, $amendAttachments, $currentLevel, $actionType, $totalValue, $modelName);
            $lr->revision_number = $revisionNumber;
            $lr->approval_level = 1;
            $lr->revision_date = now();
            $amendAfterStatus = $approveDocument['approvalStatus'] ?? $currentStatus;
            $lr->document_status = $amendAfterStatus;
            $lr->save();
        } else {
            if ($request->document_status == ConstantHelper::SUBMITTED) {
            $revisionNumber = $lr->revision_number ?? 0;
            $actionType = 'submit';
            $totalValue =  $lr->total_charges ?? 0;
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
            $document_status = $approveDocument['approvalStatus'];
            $lr->document_status = $document_status;
             $lr->save();
            } else {
                $lr->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                $lr->save();
            }
        }
        
      
       
        $this->handleLorryMediaUploads($request, $lr);


        $lr->locations()->delete();

        foreach ($request->locations as $location) {
            ErpLogisticsLrLocation::create([
                'lorry_receipt_id' => $lr->id,
                'location_id'      => $location['location_id'],
                'type'             => $location['type'],
                'no_of_articles'   => $location['no_of_articles'],
                'weight'           => $location['weight'],
                'amount'           => $location['freight'] ?? 0,
            ]);
        }

       
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the driver',
                'error' => $e->getMessage(),
            ], 500);
        }
}


protected function handleLorryMediaUploads(Request $request, ErpLorryReceipt $lr)
{
    
    $removedMediaIds = explode(',', $request->input('removed_media_ids', ''));

    foreach ($removedMediaIds as $mediaId) {
        if (!$mediaId) continue;

        $media = ErpLogisticLRMedia::find($mediaId);
        if ($media) {
            // Delete file from storage
            Storage::disk($media->disk)->delete('lorry_files/' . $media->file_name);
            $media->delete();
        }
    }
    $fileInputs = [
        'attachments' => 'attachments',
    ];

    foreach ($fileInputs as $inputKey => $collectionName) {
        if ($request->hasFile($inputKey)) {
            foreach ($request->file($inputKey) as $file) {
                $path = $file->store('lorry_files', 'public');

                ErpLogisticLRMedia::create([
                    'uuid'                  => (string) Str::uuid(),
                    'model_type'            => ErpLorryReceipt::class,
                    'model_id'              => $lr->id,
                    'model_name'            => 'ErpLorryReceipt',
                    'collection_name'       => $collectionName,
                    'name'                  => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'file_name'             => basename($path),
                    'mime_type'             => $file->getMimeType(),
                    'disk'                  => 'public',
                    'size'                  => $file->getSize(),
                    'manipulations'         => json_encode([]),
                    'custom_properties'     => json_encode([]),
                    'generated_conversions' => json_encode([]),
                    'responsive_images'     => json_encode([]),
                    'lorry_column'          => null,
                ]);
            }
        }
    }
}




public function getFreePointData(Request $request)
{
    $locationId  = $request->location_id;
    $sourceId    = $request->source_id;
    $vehicleId   = $request->vehicle_id;
    $customerId  = $request->customer_id;

    $vehicle        = ErpVehicle::find($vehicleId);
    $vehicleTypeIds = (array) ($vehicle->vehicle_type_id ?? []);
    
    $amount     = null;
    $pricing    = null;
    $multiPoint = null;

    $vehicleTypeFilter = function ($q) use ($vehicleTypeIds) {
        if (!empty($vehicleTypeIds)) {
            $q->where(function ($inner) use ($vehicleTypeIds) {
                foreach ($vehicleTypeIds as $id) {
                    $inner->orWhereJsonContains('vehicle_type_id', (string) $id);
                }
            });
        }
    };

    $pricing = ErpLogisticsMultiFixedPricing::where('source_route_id', $sourceId)
        ->where($vehicleTypeFilter)
        ->where('customer_id', $customerId)
        ->where('status', 'active')
        ->first()
        ?? ErpLogisticsMultiFixedPricing::where('source_route_id', $sourceId)
            ->where($vehicleTypeFilter)
            ->where(fn($q) => $q->whereNull('customer_id')->orWhere('customer_id', ''))
            ->where('status', 'active')
            ->first()
        ?? ErpLogisticsMultiFixedPricing::where('source_route_id', $sourceId)
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->first()
        ?? ErpLogisticsMultiFixedPricing::where('source_route_id', $sourceId)
            ->where('status', 'active')
            ->first();

    if ($pricing) {
        $matchedLocation = ErpLogisticsMultiFixedLocation::where('location_route_id', $locationId)
            ->where('multi_fixed_pricing_id', $pricing->id)
            ->first();
        if ($matchedLocation) {
            $amount = $matchedLocation->amount;
        }
    }
    $multiPoint = ErpLogisticsMultiPointPricing::withDefaultGroupCompanyOrg()
        ->where('source_route_id', $sourceId)
        ->where('customer_id', $customerId)
        ->first()
        ?? ErpLogisticsMultiPointPricing::withDefaultGroupCompanyOrg()
            ->where('source_route_id', $sourceId)
            ->where(fn($q) => $q->whereNull('customer_id')->orWhere('customer_id', ''))
            ->first()
        ?? ErpLogisticsMultiPointPricing::withDefaultGroupCompanyOrg()
            ->where('source_route_id', $sourceId)
            ->first();

    if ($pricing && $multiPoint) {
        return response()->json([
            'status'       => 'both_exist',
            'amount'       => $amount ?? 0,
            'free_point'   => $multiPoint->free_point,
            'free_amount'  => $multiPoint->amount,
        ]);
    }

    if ($pricing) {
        return response()->json([
            'status' => 'exists_in_fixed',
            'amount' => $amount ?? '',
        ]);
    }

    if ($multiPoint) {
        return response()->json([
            'status'      => 'free_point',
            'free_point'  => $multiPoint->free_point,
            'free_amount' => $multiPoint->amount,
        ]);
    }

    return response()->json(['status' => 'not_found']);
}



 public function revoke(Request $request)
    {
        DB::beginTransaction();
        try {
            $lr = ErpLorryReceipt::find($request->id);
            if (isset($lr)) {
                 $revoke = Helper::approveDocument($lr->book_id, $lr->id, $lr->revision_number, '', [], 0, ConstantHelper::REVOKE, $lr->total_charges, get_class($lr));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    
                    $lr->document_status = $revoke['approvalStatus'];
                    $lr->save();
                    DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Revoked successfully',
                    ]);
                }
            } else {
                DB::rollBack();
                throw new ApiGenericException("No Document found");
            }
        } catch(Exception $ex) {
            DB::rollBack();
            throw new ApiGenericException($ex->getMessage());
        }
    }
   
public function destroy($id)
{
    DB::beginTransaction();

    try {
        $lr = ErpLorryReceipt::findOrFail($id);
        $lr->mediaAttachments()->delete();
        $lr->locations()->delete();

     
        $lr->delete();

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Record deleted successfully'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'An error occurred while deleting the item: ' . $e->getMessage()
        ], 500);
    }
 }

 public function buildLorryReceiptPdf($id)
{
    $user = Helper::getAuthenticatedUser();
    $segments = request()->segments(); 
    $pathUrl = $segments['0'].'/'.$segments['1'];
    $pathUrl = str_replace('/', '_', $pathUrl);
    $redirectUrl = route('logistics.lorry-receipt.generate-pdf', $id); 
    $lorryReceiptType = ConstantHelper::LR_SERVICE_ALIAS;

    $lorryReceipt = ErpLorryReceipt::with([
        'source', 
        'destination', 
        'driver', 
        'vehicleType', 
        'consignor', 
        'consignee', 
        'locations'
    ])-> bookViewAccess($pathUrl)
    ->withDefaultGroupCompanyOrg()
    -> withDraftListingLogic()->where('id', $id)
    ->firstOrFail();

    $organization = Organization::find($user->organization_id);
    $organizationAddress = Address::with(['city', 'state', 'country'])
        ->where('addressable_id', $user->organization_id)
        ->where('addressable_type', Organization::class)
        ->first();

    $imagePath = public_path('assets/css/midc-logo.jpg');
    $locationPathFirst = public_path('img/lorry/green-loc.png');
    $locationPathSecond = public_path('img/lorry/loca-red.jpg');

    $pdf = Pdf::loadView('pdf.lorry-receipt-print', [
        'lorryReceipt' => $lorryReceipt,
        'organization' => $organization,
        'organizationAddress' => $organizationAddress,
        'imagePath' => $imagePath,
        'locationPathFirst' => $locationPathFirst,
        'locationPathSecond' => $locationPathSecond,
    ]);

    return $pdf;

}
 public function generatePdf(Request $request, $id)
{
    $pdf = $this->buildLorryReceiptPdf($id);
    return $pdf->stream('lorry-receipt-preview.pdf');
}


public function lorryMail(Request $request)
{
    $request->validate([
        'email_to' => 'required|email',
    ], [
        'email_to.required' => 'Recipient email is required.',
        'email_to.email'    => 'Please enter a valid email address.',
    ]);

    $lr = ErpLorryReceipt::with(['consignee'])->findOrFail($request->id);
    $consignee = $lr->consignee;

    $sendTo = $request->email_to;
    $consignee->email = $sendTo;
    $cc = $request->cc_to ? implode(',', $request->cc_to) : null;
    $remarks = $request->remarks ?? '';
    $title = "Lorry Receipt Generated";
    $mail_from = '';
    $mail_from_name = '';
    $name = $consignee->company_name ?? 'Customer';

    $encryptedEmail = Crypt::encryptString($consignee->email);
    $approveLink = route('lorry-receipt.approve', ['id' => $lr->id, 'email' => $encryptedEmail]); 
    
    $description = <<<HTML
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-family: Arial, sans-serif;">
      
        <tr>
            <td>
                <h2 style="color: #2c3e50;">Your Lorry Receipt</h2>
                <p style="font-size: 16px; color: #555;">Dear {$name},</p>
                <p style='font-size: 15px; color: #333;'>{$remarks}</p>
                <p style="font-size: 15px; color: #333;">The receipt is attached with this email. Please review it at your convenience</p>

               <p></p>

                <p style="text-align: center; margin: 20px 0;">
                    <a href="{$approveLink}" target="_blank" style="background-color: #7415ae; color: #ffffff; padding: 12px 24px; border-radius: 5px; font-size: 16px; text-decoration: none; font-weight: bold;">
                        Approve Receipt
                    </a>
                </p>
            </td>
        </tr>
    </table>
    HTML;

    $pdf = $this->buildLorryReceiptPdf($request->id);
    $pdfFilename = 'lorry_receipt_' . $request->id . '_' . time() . '.pdf';
    $pdfPath = storage_path("app/temp_mails/{$pdfFilename}");

    if (!file_exists(dirname($pdfPath))) {
        mkdir(dirname($pdfPath), 0777, true);
    }

    file_put_contents($pdfPath, $pdf->output());

    $htmlFilename = 'lorry_receipt_preview_' . time() . '.html';
    $htmlPath = storage_path("app/temp_mails/{$htmlFilename}");
    file_put_contents($htmlPath, $description);

    $attachments = [
        [
            'file' => file_get_contents($pdfPath),
            'options' => [
                'as' => $pdfFilename,
                'mime' => 'application/pdf'
            ]
        ],
     
    ];

    return $this->sendMail($consignee, $title, $description, $cc, $attachments, $mail_from, $mail_from_name);
}
 public function sendMail($receiver, $title, $description, $cc = null, $attachments = [], $mail_from = null, $mail_from_name = null,$bcc=null)
    {
        try {
            if (!$receiver || !isset($receiver->email)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Receiver details are missing or invalid.',
                ], 400);
            }
            $storedAttachments = [];

            foreach ($attachments as $attachment) {
                $filename = $attachment['options']['as'] ?? uniqid() . '.pdf';
                $mime = $attachment['options']['mime'] ?? 'application/octet-stream';
                $tempPath = storage_path("app/temp_mails/{$filename}");

                if (!file_exists(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0777, true);
                }

                file_put_contents($tempPath, $attachment['file']);

                $storedAttachments[] = [
                    'path' => $tempPath,
                    'as' => $filename,
                    'mime' => $mime
                ];
            }

            dispatch(new SendEmailJob(
            $receiver,
            $mail_from,
            $mail_from_name,
            $title,
            $description,
            $cc,
            $bcc,
            $storedAttachments
            ));

            return response()->json([
                'status' => 'success',
                'message' => 'Email request sent successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending email: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send email. ' . $e->getMessage(),
            ], 500);
        }
    }

public function approveReceipt($id, $encryptedEmail)
{
    $email = Crypt::decryptString($encryptedEmail);

    $customer = \DB::table('erp_customers')->where('email', $email)->first();

    $data = [
        'name' => $customer ? $customer->company_name : 'User',
        'remarks' => '',
        'status' => '',
    ];

    if (!$customer) {
        $data['remarks'] = 'Unauthorized: Email not found in records.';
        $data['status'] = 'error';
        return view('logistics.lorry-receipt.success', $data);
    }

    $lr = \DB::table('erp_logistics_lorry_receipt')->where('id', $id)->first();

    if (!$lr) {
        $data['remarks'] = 'Lorry Receipt not found.';
        $data['status'] = 'error';
        return view('logistics.lorry-receipt.success', $data);
    }

     if ($lr->consignee_status == 'approved') {
        $data['remarks'] = 'Your Lorry Receipt has been already approved!.';
        $data['status'] = 'success';
        return view('logistics.lorry-receipt.success', $data);
    }


   $consignee = null;
    if ($lr->consignee_id) {
        $consignee = \DB::table('erp_customers')->where('id', $lr->consignee_id)->first();
    }

    if (!$consignee || $consignee->email !== $email) {
        $data['remarks'] = 'Unauthorized: Email does not match consignee. You are not allowed to approve this receipt.';
        $data['status'] = 'error';
        return view('logistics.lorry-receipt.success', $data);
    }

   \DB::table('erp_logistics_lorry_receipt')
    ->where('id', $id)
    ->update(['consignee_status' => 'approved']);

    $data['remarks'] = 'Your Lorry Receipt has been successfully approved!';
    $data['status'] = 'success';

    return view('logistics.lorry-receipt.success', $data);
}




}
