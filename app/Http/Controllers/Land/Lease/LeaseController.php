<?php

namespace App\Http\Controllers\Land\Lease;

use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Land;
use App\Models\Lease;
use App\Models\State;
use App\Helpers\Helper;
use App\Helpers\TaxHelper;
use App\Models\AuthUser;
use App\Models\Country;
use App\Models\Customer;
use App\Models\LandLease;
use App\Models\LandLeaseAction;
use App\Models\LandLeaseHistory;
use App\Models\CurrencyExchange;
use App\Models\LandParcel;
use App\Models\ErpDocument;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\LandLeasePlot;
use App\Models\NumberPattern;
use App\Helpers\CurrencyHelper;
use App\Models\LandLeaseAddress;
use App\Models\LandLeaseDocument;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LandLeaseOtherCharges;
use Illuminate\Support\Facades\Response;
use App\Models\LandLeaseScheduler;
use App\Http\Requests\Lease\CreateLeaseRequest;
use App\Http\Controllers\LandNotificationController;
use App\Models\Employee;
use App\Models\User;
use App\Models\ErpItem;
use App\Models\ErpAddress;

class LeaseController extends Controller
{
    public function index()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $userData = Helper::userCheck();

        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $type = $userData['type'];
        $user_type = $userData['user_type'];


        $leasesQuery = LandLease::with('series', 'plots.land', 'customer')
            ->where('organization_id', $organization_id);

        // Fetch distinct values without altering the main query
        $document_no = (clone $leasesQuery)->distinct()->pluck('document_no');
        $selectedStatus = (clone $leasesQuery)->distinct()->pluck('approvalStatus');

        // Fetch all leases with ordering
        $leases = $leasesQuery->orderby('id', 'desc')->get();

        $selectedDateRange = '';
        $pincode = '';
        $land_no = '';
        return view('land.lease.index', compact('leases', 'selectedDateRange', 'document_no', 'land_no', 'selectedStatus')); // Return the 'land.onlease' view
    }

    public function show(Request $r, $id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $userData = Helper::userCheck();
        $type = $userData['type'];
        $user_type = $userData['user_type'];

        $parentURL = request()->segments()[0];


        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $customers = Customer::withDefaultGroupCompanyOrg()->with(['erpOrganizationType', 'category', 'subcategory', 'addresses', 'currency'])->get();
        //$currencies = Currency::where('organization_id', $organizationId)->get();
        $countries = Country::where('id', '101')->get();
        $states = State::where('country_id', '101')->get();
        $doc_type = ErpDocument::where('organization_id', $organization_id)
            ->where('service', 'land')->where('status', 'active')
            ->get();
        $lands = LandParcel::where('organization_id', $organization_id)
            ->get();


        $currNumber = $r->revisionNumber;
        if ($currNumber != "") {
            $lease = LandLeaseHistory::where('source_id', $id)->first(); // Fetch all leases




        } else {
            $lease = LandLease::where('organization_id', $organization_id)->findOrFail($id); // Fetch all leases
    }
        if (isset($lease->leaseable_type)) {
            $creatorType = explode("\\", $lease->leaseable_type);
            $creatorType = strtolower(end($creatorType));
        }
        $creatorType = "";



        $buttons = Helper::actionButtonDisplay(
            $lease->book_id,
            $lease->approvalStatus,
            $id,
            $lease->total_amount,
            $lease->approvalLevel,
            $lease->leaseable_id,
            $creatorType,
            $lease->revision_number
        );

        $history = Helper::getApprovalHistory($lease->book_id, $id, $lease->revision_number, $lease->total_amount);

        $revisionNumbers = $history->pluck('revision_number')->unique()->values()->all();


        $page = 'view_detail';
        $approvers = AuthUser::where('organization_id',$organization_id)->get();




        return view('land.lease.show', compact('approvers','currNumber', 'page', 'revisionNumbers', 'buttons', 'history', 'lease', 'book_type', 'customers', 'countries', 'states', 'doc_type', 'lands')); // Return the 'land.onlease' view
    }
    public function destroy($id)
    {
        try {
            $bank = LandLease::findOrFail($id);
            $referenceTables = [
                'erp_land_lease_plots' => ['lease_id'],
                'erp_land_lease_other_charges' => ['lease_id'],
                'land_lease_addresses' => ['lease_id'],
                'erp_land_lease_documents' => ['lease_id'],
                'erp_land_leases_actions' => ['source_id'],
            ];
            $result = $bank->deleteWithReferences($referenceTables);

            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the lease ',
            ], 500);
        }
    }
    public function edit($id)
    {
        $user = Helper::getAuthenticatedUser();
        $auth_user = Helper::getAuthenticatedUser()->auth_user_id;
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $userData = Helper::userCheck();
        $type = $userData['type'];
        $user_type = $userData['user_type'];

        $lease = LandLease::where('organization_id', $organization_id)
            ->where('leaseable_id', $auth_user)->findOrFail($id); // Fetch all leases
        $parentURL = request()->segments()[0];


        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $customers = Customer::with(['erpOrganizationType', 'category', 'subcategory', 'addresses', 'currency'])
            ->where('organization_id', $organizationId)
            ->get();
        //$currencies = Currency::where('organization_id', $organizationId)->get();
        $countries = Country::where('id', '101')->get();
        $states = State::where('country_id', '101')->get();
        $doc_type = ErpDocument::where('organization_id', $organization_id)
            ->where('service', 'land')->where('status', 'active')
            ->get();
        $lands = LandParcel::whereHas('plots', fn($query) => 
            $query->whereDoesntHave('leasePlots')
        )->with('plots')
        ->where('organization_id', $organization_id)
        ->get();
    
            // Filter out lands that do not contain LEASE_SERVICE_TYPE in their service items
            $lands = $lands->filter(function ($land) {
            $serviceItems = json_decode($land->service_item, true);
    
            if (!empty($serviceItems)) {
                // Remove extra single quotes from array keys
                $cleanedItems = array_map(function ($item) {
                    return array_combine(
                        array_map(fn($key) => trim($key, "'"), array_keys($item)), // Trim single quotes from keys
                        $item
                    );
                }, $serviceItems);
    
                // Check if any service type matches the lease service types
                foreach ($cleanedItems as $item) {
                    if (in_array($item['servicetype'], ConstantHelper::LEASE_SERVICE_TYPE)) {
                        return true; // Keep this land
                    }
                }
            }
            return false; // Remove this land
            })->values(); // Reset array keys


            $selectedLand = LandLease::find($id)->land;
            
            
            if ($selectedLand && !$lands->contains('id', $selectedLand->id)) {
                $lands->push($selectedLand); // Add only if it's not already in the collection
            }

        
        
        $creatorType = explode("\\", $lease->leaseable_type);
        $creatorType = strtolower(end($creatorType));


        $buttons = Helper::actionButtonDisplay(
            $lease->book_id,
            $lease->approvalStatus,
            $id,
            $lease->total_amount,
            $lease->approvalLevel,
            $lease->leaseable_id,
            $creatorType,
            $lease->revision_number
        );


        return view('land.lease.edit', compact('buttons', 'lease', 'book_type', 'customers', 'countries', 'states', 'doc_type', 'lands')); // Return the 'land.onlease' view
    }
    public function taxCalculation(Request $request)
{
    $user = Helper::getAuthenticatedUser();
    $organization = $user->organization;
    $firstAddress  = ErpAddress::where('addressable_id',$organization->id)->where('addressable_type',get_class($organization))->first();
    if ($firstAddress) {
        $companyCountryId = $firstAddress->country_id;
        $companyStateId = $firstAddress->state_id;
    } else {
        return response()->json(['error' => 'No address found for the organization.'], 404);
    }

    $price = $request->price ?? 6000;
    $landid = LandParcel::find($request->landid);

    $item = json_decode($landid->service_item);

    $hsnId = null;
    $itemdetail = '';

    if (!empty($item)) {
        $serviceCodes = array_filter($item, function ($items) {
            return in_array($items->{"'servicetype'"}, ConstantHelper::LEASE_SERVICE_TYPE);
        });

        // Extract service codes from the filtered data
        $itemdetail = array_map(function ($items) {
            return $items->{"'servicecode'"};
        }, $serviceCodes);
    }


    $item = Item::where('item_code', $itemdetail[0] ?? null)->first();

    if (isset($item)) {
        $hsnId = $item->hsn_id;
    } else {
        return response()->json(['error' => 'Invalid Item'], 500);
    }

    $transactionType = $request->transaction_type ?? 'sale';

    if ($transactionType === "sale") {
        $fromCountry = $companyCountryId;
        $fromState = $companyStateId;
        $upToCountry = $request->party_country_id ?? $companyCountryId;
        $upToState = $request->party_state_id ?? $companyStateId;
    } else {
        $fromCountry = $request->party_country_id ?? $companyCountryId;
        $fromState = $request->party_state_id ?? $companyStateId;
        $upToCountry = $companyCountryId;
        $upToState = $companyStateId;
    }

    try {
        $taxDetails = TaxHelper::calculateTax($hsnId, $price, $fromCountry, $fromState, $upToCountry, $upToState, $transactionType, $request->date);
        $rowCount = intval($request->rowCount ?? 1);
        $itemPrice = intval($request->price ?? 0);

        return response()->json($taxDetails);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    public function getLandParcelData($land_id)
    {
        $land_data = LandParcel::with('plot')->where('id', $land_id)->get();

        return response()->json([
            'status' => 200,
            'data' => $land_data
        ]);
    }

    public function create(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $userData = Helper::userCheck();
        $type = $userData['user_type'];

        $parentURL = request()->segments()[0];


        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $lands = LandParcel::whereHas('plots', fn($query) => 
        $query->whereDoesntHave('leasePlots')->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)
    )->with('plots')
    ->where('organization_id', $organization_id)
    ->get();

        // Filter out lands that do not contain LEASE_SERVICE_TYPE in their service items
        $lands = $lands->filter(function ($land) {
        $serviceItems = json_decode($land->service_item, true);

        if (!empty($serviceItems)) {
            // Remove extra single quotes from array keys
            $cleanedItems = array_map(function ($item) {
                return array_combine(
                    array_map(fn($key) => trim($key, "'"), array_keys($item)), // Trim single quotes from keys
                    $item
                );
            }, $serviceItems);

            // Check if any service type matches the lease service types
            foreach ($cleanedItems as $item) {
                if (in_array($item['servicetype'], ConstantHelper::LEASE_SERVICE_TYPE)) {
                    return true; // Keep this land
                }
            }
        }
        return false; // Remove this land
        })->values(); // Reset array keys

        
    
        $user = Helper::getAuthenticatedUser();

        $organizationId = $user->organization_id;
        $customers = Customer::withDefaultGroupCompanyOrg()->with(['erpOrganizationType', 'category', 'subcategory', 'addresses', 'currency'])->get();
        //$currencies = Currency::where('organization_id', $organizationId)->get();
        $countries = Country::get();
        $states = State::get();
        $doc_type = ErpDocument::where('organization_id', $organization_id)
            ->where('service', 'land')->where('status', 'active')
            ->get();


        return view('land.lease.create', compact('book_type', 'lands', 'customers', 'countries', 'states', 'doc_type')); // Redirect back to the on lease page
    }

    public function store(CreateLeaseRequest $request)
{
    try {
        DB::beginTransaction();

        $lease = LandLease::createUpdateLease($request);

        if (!$lease) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create or update lease.']);
        }

        LandLeaseAddress::createUpdateAddress($request, $lease);
        //LandLeaseDocument::createUpdateDocument($request, $lease);
        LandLeasePlot::createPlot($request, $lease);

        if (!empty($request->other_charges)) {
            LandLeaseOtherCharges::createOtherCharges($request, $lease);
        }

        if (!empty($request->sc)) {
            LandLeaseScheduler::createUpdateScheduler($request, $lease->id);
        }

        DB::commit();
        return redirect()->route('lease.index')->with('success', 'Lease information saved successfully.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return redirect()->back()->withInput()->withErrors(['error' => 'Validation error occurred. Please check your input.'])->withErrors($e->validator->errors());

    } catch (\Throwable $e) {
        DB::rollBack();
        return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while saving the data. Please try again.'.$e->getMessage()]);
    }
}

    public function update(CreateLeaseRequest $request)
    {
        // Attempt to save the data
        $edit_id = $request->edit_id;
        try {
            DB::beginTransaction();

            $lease = LandLease::createUpdateLease(request: $request, edit_id: $edit_id);
            if ($lease) {
                LandLeaseAddress::createUpdateAddress(request: $request, lease: $lease, edit_lease_id: $edit_id);
                //LandLeaseDocument::createUpdateDocument(request: $request, lease: $lease);
                LandLeasePlot::createPlot(request: $request, lease: $lease, edit_lease_id: $edit_id);
                if (!empty($request->other_charges) && count($request->other_charges) > 0) {
                    LandLeaseOtherCharges::createOtherCharges(request: $request, lease: $lease, edit_lease_id: $edit_id);
                }
                if (!empty($request->sc) && count($request->sc) > 0) {
                    LandLeaseScheduler::createUpdateScheduler($request, $lease->id);
                }


                DB::commit();
                return redirect()->route('lease.index')->with('success', 'Lease information saved successfully.');
            } else {
                return redirect()->route('lease.edit', $edit_id)->with('error', 'Something went wrong.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // Redirect back with input data and an error message if something goes wrong
            return redirect()->back()->withInput()->withErrors(['error' => "An error occurred while saving the data. "]);
        }
    }
    public function amendment(Request $request, $id)
    {
        $land_id = LandLease::find($id);
        if (!$land_id) {
            return response()->json(['data' => [], 'message' => "Land Lease not found.", 'status' => 404]);
        }

        $revisionData = [
            ['model_type' => 'header', 'model_name' => 'LandLease', 'relation_column' => ''],
        ];

        $a = Helper::documentAmendment($revisionData, $id);
        DB::beginTransaction();
        try {
            if ($a) {
                Helper::approveDocument($land_id->book_id, $land_id->id, $land_id->revision_number, 'Amendment', $request->file('attachment'), $land_id->approvalLevel, 'amendment', $land_id->total_amount);

                $land_id->approvalStatus = ConstantHelper::DRAFT;
                $land_id->revision_number = $land_id->revision_number + 1;
                $land_id->revision_date = now();
                $land_id->save();
            }

            DB::commit();
            return response()->json(['data' => [], 'message' => "Amendment done!", 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Amendment Submit Error: ' . $e->getMessage());
            return response()->json(['data' => [], 'message' => "An unexpected error occurred. Please try again.", 'status' => 500]);
        }
    }


    public function getExchangeRate($id)
    {

        $date = Carbon::now();
        try {
            $exchangeRate = CurrencyHelper::getCurrencyExchangeRates($id, $date);

            $data = array(
                'currency_id' => $exchangeRate['data']['org_currency_id'],
                'currency_code' => $exchangeRate['data']['org_currency_code'],
                'exchange_rate_id' => $exchangeRate['data']['org_currency_exg_rate'],
                'exchange_rate' => $exchangeRate['data']['org_currency_exg_rate']
            );

            return response()->json($data);
        } catch (\Exception $e) {
            return 'error';
        }
    }

    public function customerAddressStore(Request $request)
    {

        // Validate the request
        $validatedData = $request->validate([
            'lease_id' => 'required|exists:leases,id',
            'customer_id' => 'required|exists:erp_customers,id',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string|max:255',
            'postalcode' => 'required|string|max:10',
        ]);

        // Return a success message or a redirect
        return response()->json([
            'message' => 'Customer address saved successfully!',
            'data' => '', //$customerAddress
        ]);
    }

    public function leaseFilterLand(Request $request, $page='create')
    {
        $land_id = $request->query('landId');
        $plot_id = $request->query('plotId');
        $districted_id = $request->query('districtId');
        $state_id = $request->query('stateId');

        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $userData = Helper::userCheck();
        $type = $userData['user_type'];

        $query = LandParcel::whereHas('plots', fn($q) => 
            $q->whereDoesntHave('leasePlots')->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)
        )->with('plots')
        ->where('organization_id', $organization_id);

         if ($land_id) {
            $query->where('id', $land_id);
        }
        if ($plot_id) {
            $query->whereHas('plots', function ($q) use ($plot_id) {
                $q->where('id', $plot_id);
            });
        }
        if ($districted_id) {
            $query->where('id', $districted_id);
        }
        if ($state_id) {
            $query->where('state', $state_id);
        }
        $land_filter_list = $query->get();

        return Response::json(compact('land_filter_list'));
    }
    public function leasefilter(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $userData = Helper::userCheck();
        $type = $userData['type'];
        $user_type = $userData['user_type'];


        $query = LandLease::where('organization_id', $organization_id)
            ->where('leaseable_id', $user->id)
            ->orderby('id', 'desc');
        $document_no = $query->distinct()->pluck('document_no');
        $selectedStatus = $query->distinct()->pluck('approvalStatus');
        // Filter by date range
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $start_date = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
        }

        // Filter by pincode (from the `land` table)
        if ($request->fillFed('document_no')) {
            $query->where('document_no', 'like', '%' . $request->document_no . '%');
        }

        // Filter by status
        if ($request->filled('selectedStatus')) {
            $query->where('approvalStatus', $request->selectedStatus);
        }



        $leases = $query->get();

        $selectedDateRange = '';
        $pincode = '';
        $land_no = '';
        return view('land.lease.index', compact('leases', 'selectedDateRange', 'document_no', 'land_no', 'selectedStatus')); // Return the 'land.onlease' view

    }
    public function ApprReject(Request $request)
    {
        $attachments = null;
        if ($request->has('appr_rej_doc')) {
            $path = $request->file('appr_rej_doc')->store('lease_documents', 'public');
            $attachments = $path;
        } elseif ($request->has('stored_appr_rej_doc')) {
            $attachments = $request->stored_appr_rej_doc;
        } else {
            $attachments = null;
        }

        $update = LandLease::find($request->appr_rej_lease_id);
        $approveDocument = Helper::approveDocument($update->book_id, $update->id, $update->revision_number, $request->appr_rej_remarks, $attachments, $update->approvalLevel, $request->appr_rej_status);
        $update->approvalLevel = $approveDocument['nextLevel'];
        $update->approvalStatus = $approveDocument['approvalStatus'];
        $update->appr_rej_recom_remark = $request->appr_rej_remarks ?? null;
        $update->appr_rej_doc = $attachments;
        $update->appr_rej_behalf_of = $request->appr_rej_behalf_of ? json_encode($request->appr_rej_behalf_of) : null;

        $update->save();

        $created_by = $update->leaseable_id;
        $creator = AuthUser::find($created_by);
        $approver = Helper::getAuthenticatedUser();


        if ($request->appr_rej_status == 'approve') {
            LandNotificationController::notifyLeaseApproved($creator->authUser(), $update, $approver);
            return redirect()->route("lease.index")->with(
                "success",
                "Approved Successfully!"
            );
        } else {
            LandNotificationController::notifyLeaseReject($creator->authUser(), $update, $approver);

            return redirect()->route("lease.index")->with(
                "success",
                "Rejected Successfully!"
            );
        }
    }
    public function action(Request $request)
    {
        // Validate request data
        $request->validate([
            'source_id' => 'required|exists:erp_land_leases,id', // Ensure source_id exists in the leases table
            'action' => 'required|string|in:terminate,close,renew,reminder',
            'comment' => 'nullable|string',
            'attachments' => 'nullable',
            'action_date' => 'nullable|date'
        ]);

        // Find the lease action (if needed, based on your logic)

        // Pass the action and the entire request to performAction
        $result = LandLeaseAction::performAction($request);

        if ($result['type'] == "success") {
            $update = LandLease::find($request->source_id);
            $bookId = $update->book_id;
            $docId = $update->id;
            $remarks = $request->comment;
            $attachments = $request->file('attachments');
            $currentLevel = $update->approvalLevel;
            $revisionNumber = $update->revision_number ?? 0;
            $actionType = $request->action; // Approve // reject // submit
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType);

            return redirect()->route('lease.index')->with('success', $result['message']);
        } else
            return redirect()->back()->with('error', $result['message']);
    }
    public function report(Request $request)
    {
        // Retrieve authenticated user and organization ID
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization->id;

        // Check if the request is an AJAX call
        if ($request->ajax()) {
            // Fetch the base data
            $dataQuery = LandLease::where('organization_id', $organizationId)
                ->with('customer')
                ->whereHas('land')
                ->orderBy('id', direction: 'desc')->whereIn('approvalStatus',[ConstantHelper::APPROVED,ConstantHelper::APPROVAL_NOT_REQUIRED]);

            // Apply filters based on request parameters
            if ($request->lease) {
                $dataQuery->where('document_no', $request->lease);
            }

            if ($request->land) {
                $dataQuery->where('land_id', $request->land);

                
            }

            if ($request->area) {
                $dataQuery->whereHas('land', function ($query) use ($request) {
                    $query->whereRaw("CONCAT(plot_area, '(', area_unit, ')') LIKE ?", ["%{$request->area}%"]);
                });
            }
            if ($request->dateRange) {
                $dateRange = explode(' to ', $request->dateRange);
                $dataQuery->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
            }

            // Return processed data in DataTables format
            return DataTables::of($dataQuery)
                ->addIndexColumn() // Automatically adds DT_RowIndex

                ->addColumn('lease_id', function ($data) {
                    return $data->document_no;
                })
                ->addColumn('land_no', function ($data) {
                    return $data->land->document_no ?? 'N/A';
                })
                
                ->addColumn('tenant_name', function ($data) {
                    return $data->customer ? $data->customer->display_name : 'N/A';
                })
                ->addColumn('property_name', function ($data) {
                    if ($data->plots_display() && $data->plots && $data->land) {
                        return $data->land->name . '(' . $data->plots_display() . ')';
                    }
                    return 'N/A';
                })
                ->addColumn('khasara_no', function ($data) {
                    if ($data->plots_display() && $data->plots && $data->land) 
                    return $data->land->khasara_no;
                    else
                        return 'N/A';
                })
                ->addColumn('land_area', function ($data) {
                    if($data->land)
                        return $data->land->plot_area."(".$data->land->area_unit.")" ?? "N/A";
                    else "N/A";
                })
                
                ->addColumn('lease_start_date', function ($data) {
                    return Carbon::parse($data->lease_start_date)->format('d-m-Y');
                })
                ->addColumn('lease_end_date', function ($data) {
                    return Carbon::parse($data->lease_end_date)->format('d-m-Y');
                })
                ->addColumn('monthly_rent', function ($data) {
                    return '' . number_format($data->installment_amount, 2);
                })

                ->addColumn('payment_status', function ($data) {
                    $allCount = $data->schedule->count();
                    
                    $paidCount = $data->schedule->where('status', 'paid')->count();
                    if ($paidCount === $allCount &&  $paidCount > 0)
                        return 'Paid';
                    else {
                        $pay_due = $data->schedule->where('status', '!=', 'paid')
                            ->sortBy('due_date') // Order by due date in ascending order
                            ->first();
                        if(isset($pay_due->status)){

                        if ( Carbon::parse($pay_due->due_date)->toDateString() < Carbon::now()->toDateString())
                            return 'Overdue';
                        else
                            return  ucfirst($pay_due->status);
                        }
                        else
                            return '-';
                        
                    }
                })

                ->addColumn('overdue_months', function ($data) {
                    $pay_due = $data->schedule->where('status', '!=', 'paid')
                        ->sortBy('due_date') // Order by due date in ascending order
                        ->first();
                    if(isset($pay_due->due_date)){

                    $currentDate = Carbon::now();

                    // Get the number of months overdue
                    $overdueMonths = Carbon::parse($pay_due->due_date)->diffInMonths($currentDate);

                    if (Carbon::parse($pay_due->due_date)->toDateString() < $currentDate->toDateString()) {
                        if (Carbon::parse($pay_due->due_date)->between($currentDate->startOfMonth(), $currentDate->endOfMonth())) {
                            return "Overdue for this month";
                        }
                        return "Overdue for {$overdueMonths} months";
                    }else {
                        return '-';
                    }
                }
                else
                    return '-';
                })
                ->addColumn('status', function ($data) {
                    // Parse the lease_end_date as a Carbon instance
                    $leaseEndDate = Carbon::parse($data->lease_end_date);
                    
                    // Compare the lease_end_date with today's date
                    if ($leaseEndDate >= Carbon::today()) {
                        return 'Active'; // If the lease end date is today or in the future, the status is 'Active'
                    } else {
                        return 'Expired'; // If the lease end date is in the past, the status is 'Expired'
                    }
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        $leases = LandLease::where('organization_id', $organizationId)->whereHas('land')->whereIn('approvalStatus',[ConstantHelper::APPROVED,ConstantHelper::APPROVAL_NOT_REQUIRED])->get();
        $employees = Employee::get();
        $users = User::get();
        $lands = LandParcel::where('organization_id', $organizationId)->whereHas('plots')->get();



        // Return the view for the report
        return view('land.lease.report', compact('lands','leases', 'users', 'employees'));
    }
}
