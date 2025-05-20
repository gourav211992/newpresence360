<?php

namespace App\Http\Controllers\Land;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\LandPlot;
use App\Models\LandPlotHistory;
use App\Models\LandParcel;
use App\Models\BookType;
use App\Models\ErpDocument;
use App\Models\AuthUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use App\Models\PlotLocation;
use App\Http\Requests\LandPlotRequest;
use App\Http\Controllers\LandNotificationController;

class LandPlotController extends Controller
{

    public function index()
{
    $user = Helper::getAuthenticatedUser();
    $organization_id = $user->organization_id;

    $landsQuery = LandPlot::where('organization_id', $organization_id)
        ->orderByDesc('id')
        ->with('landParcel:id,name');

    $lands = $landsQuery->get();

    return view('land.land-plot.index', [
        'lands' => $lands,
        'selectedDateRange' => '',
        'pincode' => $lands->pluck('pincode')->unique(),
        'land_no' => $lands->pluck('id'),
        'selectedStatus' => $lands->pluck('approvalStatus')->unique(),
        'khasra' => '',
        'plot' => '',
    ]);
}
public function filter(Request $request)
{
    $user = Helper::getAuthenticatedUser();
    $organization_id = $user->organization_id;
    $auth_user = $user->auth_user_id;

    $query = LandPlot::where(function ($q) use ($organization_id, $auth_user) {
        $q->where('organization_id', $organization_id)
          ->where('user_id', $auth_user)
          ->orWhereHas('approvelworkflow', fn($q) => $q->where('user_id', $auth_user));
    })->orderByDesc('id');

    if (!empty($request->date_range)) {
        $dates = explode(' to ', $request->date_range);
        $query->whereBetween('created_at', count($dates) > 1 
            ? [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59'] 
            : [$dates[0], $dates[0]]);
    }

    foreach (['land_no' => 'id', 'pincode' => 'pincode', 'selectedStatus' => 'approvalStatus'] as $key => $column) {
        if (!empty($request->$key)) {
            $query->where($column, $request->$key);
        }
    }

    $lands = $query->get();

    return view('land.land-plot.index', [
        'lands' => $lands,
        'selectedDateRange' => '',
        'pincode' => $lands->pluck('pincode')->unique(),
        'land_no' => $lands->pluck('id'),
        'selectedStatus' => $lands->pluck('approvalStatus')->unique(),
        'khasra' => '',
        'plot' => '',
    ]);
}

public function create()
{
    $user = Helper::getAuthenticatedUser();
    $organization_id = $user->organization_id;
    $parentURL = request()->segment(1);
    $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
    if (empty($servicesBooks['services'])) {
        return redirect()->route('/');
    }

    $series = Helper::getBookSeriesNew($servicesBooks['services'][0]->alias, $parentURL)->get();
    $lands = LandParcel::with('plot')
        ->where('organization_id', $organization_id)
        ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
        ->latest()->get();

    $doc_type = ErpDocument::where('organization_id', $organization_id)
        ->where(['service' => 'land', 'status' => 'active'])
        ->get();

    return view('land.land-plot.add', compact('series', 'lands', 'doc_type'));
}

    public function search(Request $request)
{
    $query = LandParcel::query();

    if ($request->has('district') && $request->district != '') {
        $query->where('district', $request->district);
    }

    if ($request->has('state') && $request->state != '') {
        $query->where('state', $request->state);
    }

    if ($request->has('country') && $request->country != '') {
        $query->where('country', $request->country);
    }

    $lands = $query->get();

    return response()->json(['lands' => $lands]);
}

public function save(LandPlotRequest $request)
{
    $user = Helper::getAuthenticatedUser();
    $organization = $user->organization;
    $organization_id = $organization->id;
    $group_id = $organization->group_id;
    $company_id = $organization->company_id;
    $auth_user = $user->auth_user_id;
    $type = $user->authenticable_type;

    try {
        $geofence = "";
        $status = $request->status_val;
        $userData = Helper::userCheck();
        $json = [];

        if ($request->has('documentname')) {
            foreach ($request->documentname as $key => $names) {
                $json[$key]['name'] = $names;

                if (isset($request->attachments[$key])) {
                    foreach ($request->attachments[$key] as $key1 => $document) {
                        $documentName = time() . '-' . $document->getClientOriginalName();
                        $document->move(public_path('documents'), $documentName);
                        $json[$key]['files'][$key1] = $documentName;
                    }
                }
            }
        }

        $validatedData = array_merge($request->validated(), [
            'organization_id' => $organization_id,
            'user_id' => $auth_user,
            'type' => $type,
            'attachments' => json_encode($json),
            'approvalStatus' => $status,
            'approvalLevel' => 1,
            'landable_id' => $userData['user_id'],
            'landable_type' => $userData['user_type'],
            'book_id' => $request->series,
            'group_id' => $group_id,
            'company_id' => $company_id,
            'document_date' => Carbon::now()->format('Y-m-d'),
            'geofence_file' => $geofence,
        ]);
        
        $landPlot = LandPlot::create($validatedData);
        $update = LandPlot::find($landPlot->id);

        if ($status == ConstantHelper::SUBMITTED) {
            $actionType = 'submit';
            $approveDocument = Helper::approveDocument(
                $update->book_id, $update->id, $update->revision_number ?? 0,
                $update->remarks, $request->attachments, $update->approvalLevel, $actionType
            );

            $document_status = Helper::checkApprovalRequired($update->book_id);
            $update->approvalStatus = $document_status;
            $update->save();

            if ($document_status == ConstantHelper::SUBMITTED && $update->approvelworkflow->count() > 0) {
                foreach ($update->approvelworkflow as $approver) {
                    if ($approver->user) {
                        LandNotificationController::notifyLandPlotSubmission($approver->user, $update);
                    }
                }
            }
        } else {
            Helper::approveDocument(
                $update->book_id, $update->id, $update->revision_number ?? 0,
                $update->remarks, $request->attachments, $update->approvalLevel, 'draft'
            );
        }

        if ($request->hasFile('geofence')) {
            $file = $request->file('geofence');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            foreach ($csvData as $key => $row) {
                if ($key != 0) {
                    PlotLocation::create([
                        'land_plot_id' => $landPlot->id,
                        'latitude' => $row[0],
                        'longitude' => $row[1],
                    ]);
                }
            }
        }

        return redirect('/land-plot')->with('success', 'Land Plot Added successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->withInput()->withErrors(['error' => "An unexpected error occurred. Please try again".$e->getMessage()]);
    }
}

public function edit($id)
{
    $user = Helper::getAuthenticatedUser();
    $organization = $user->organization;
    $organization_id = $organization->id;
    $group_id = $organization->group_id;
    $company_id = $organization->company_id;
    $auth_user = $user->auth_user_id;
    $type = $user->authenticable_type;

    // Fetch land parcels with approved status
    $lands = LandParcel::with('plot')
        ->where('organization_id', $organization_id)
        ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
        ->get();

    // Fetch active document types for land service
    $doc_type = ErpDocument::where('organization_id', $organization_id)
        ->where('service', 'land')
        ->where('status', 'active')
        ->get();

    $parentURL = request()->segment(1);
    $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
    if (empty($servicesBooks['services'])) {
        return redirect()->route('/');
    }

    // Get the first service and fetch its book series
    $firstService = $servicesBooks['services'][0];
    $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();

    $data = LandPlot::where('organization_id', $organization_id)
        ->where('user_id', $auth_user)
        ->with('landParcel:id,name')
        ->findOrFail($id);
    
        $locations = PlotLocation::where('land_plot_id', $id)->get();

    $creatorType = explode("\\", $data->landable_type);
    $amount = $data->plot_valuation ?? 0;
    $buttons = Helper::actionButtonDisplay($data->book_id, $data->approvalStatus, $id, $amount, $data->approvalLevel, $data->landable_id, strtolower(end($creatorType)));
    $history = Helper::getApprovalHistory($data->book_id, $id, 0);

    $page = "edit";

    return view('land.land-plot.edit', compact('series', 'data', 'lands', 'doc_type', 'creatorType', 'locations', 'buttons', 'history'));
}
 public function view(Request $r,$id)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id= $organization->id;
        $group_id= $organization->group_id;
        $company_id=  $organization->company_id;
        $auth_user = Helper::getAuthenticatedUser()->auth_user_id;
        $type =$user->authenticable_type;
       
        $lands = LandParcel::with('plot')
        ->where('organization_id', $organization_id)
        ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
        ->get();
        
            $doc_type = ErpDocument::where('organization_id', $organization_id)
            ->where('service', 'land')->where('status','active')
            ->get();


            $parentURL = request()->segment(1);
        

            $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
            if (count($servicesBooks['services']) == 0) {
               return redirect() -> route('/');
           }
           $firstService = $servicesBooks['services'][0];
           $series = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
           
        $currNumber=$r->revisionNumber;

        if ($currNumber!="") {
        $data = LandPlotHistory::where('organization_id', $organization_id)->where('source_id',$id)->first();
        $history = Helper::getApprovalHistory($data->book_id, $id, $data->revision_number);

        $locations = PlotLocation::where('land_plot_id', $id)->get();
        $data['attachments']=json_encode($data['attachments']);
    }
        else
        {
            $data = LandPlot::where('organization_id', $organization_id)
            ->with('landParcel:id,name')->find($id);
            $locations = PlotLocation::where('land_plot_id', $id)->get();
            $history = Helper::getApprovalHistory($data->book_id, $id, $data->revision_number);
        }


        $creatorType = explode("\\", $data->landable_type);

        $amount = $data->plot_valutaion==null?0:$data->plot_valutaion;
        $buttons = Helper::actionButtonDisplay($data->book_id, $data->approvalStatus, $id, $amount, $data->approvalLevel, $data->landable_id, strtolower(end($creatorType)));

        $approvers= Helper::getOrgWiseUserAndEmployees($organization_id);
        $page = "edit";
        $revisionNumbers = $history->pluck('revision_number')->unique()->values()->all();

        return view('land.land-plot.view', compact('approvers','currNumber','revisionNumbers','series', 'data','lands','doc_type','creatorType','locations','buttons','history','page')); // Return the 'land.add' view
    }
    public function update(LandPlotRequest $request)
{
    $id= $request->id;
    $validatedData=[];
    $landPlot = LandPlot::findOrFail($id); // Use findOrFail to get the record or fail with a 404
    $user = Helper::getAuthenticatedUser();
    $organization = $user->organization;
    $organization_id= $organization->id;
    $auth_user = Helper::getAuthenticatedUser()->auth_user_id;
    $type =$user->authenticable_type;
    
    $json = [];

            if ($request->has('documentname')) {
                $i = 0;
                foreach ($request->documentname as $key => $names) {
                    $json[$i]['name'] = $names; // Store the document name

                    // Handle new attachments
                    if (isset($request->attachments[$key])) {
                        foreach ($request->attachments[$key] as $key1 => $document) {
                            $documentName = time() . '-' . $document->getClientOriginalName();

                            // Move the document to the public/documents folder
                            $document->move(public_path('documents'), $documentName);

                            // Append the new file to the 'files' array for this document
                            $json[$i]['files'][] = $documentName;
                        }
                    }

                    // Handle old attachments
                    if (isset($request->oldattachments[$key])) {
                        foreach ($request->oldattachments[$key] as $key1 => $document1) {
                            // Append the old file to the 'files' array for this document
                            $json[$i]['files'][] = $document1;
                        }
                    }

                    $i++;
                }
            }
            $status = $request->status_val;

            $validatedData = $request->validated();
           
            $validatedData = array_merge($validatedData, [
                'attachments' => json_encode($json),
                'organization_id' => $organization_id,
                'user_id' => $auth_user,
                'type' => $type,
                'approvalStatus' => $status,
                ]);

    try {
        // Update the existing record
        $landPlot->update($validatedData);

        $update = $landPlot;

            $document_status= $request->status_val;
            if ($status == ConstantHelper::SUBMITTED) {
                $bookId = $update->book_id;
                $docId = $update->id;
                $remarks = $update->remarks;
                $attachments = $request->file('attachments');
                $currentLevel = $update->approvalLevel;
                $revisionNumber = $update->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
                $document_status = Helper::checkApprovalRequired($bookId);
                $update->approvalStatus = $document_status;
                $update->save();
                if($document_status==ConstantHelper::SUBMITTED){

                    if ($update->approvelworkflow->count() > 0) { // Check if the relationship has records
                        foreach ($update->approvelworkflow as $approver) {
                            if ($approver->user) { // Check if the related user exists
                                $approver_user = $approver->user;
                                LandNotificationController::notifyLandPlotSubmission($approver_user, $update);
                            }
                        }
                    }
            }
            }
            else{
                $bookId = $update->book_id;
                $docId = $update->id;
                $remarks = $update->remarks;
                $attachments = $request->file('attachments');
                $currentLevel = $update->approvalLevel;
                $revisionNumber = $update->revision_number ?? 0;
                $actionType = 'draft'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
            }



        // Handle geofence file if it exists
        if ($request->hasFile('geofence')) {
            $file = $request->file('geofence');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));


            // Clear existing plot locations if needed
            PlotLocation::where('land_plot_id', $landPlot->id)->delete();

            foreach ($csvData as $key => $row) {
                if ($key != 0) { // Skip the header row
                    PlotLocation::create([
                        'land_plot_id' => $landPlot->id,
                        'latitude' => $row[0],
                        'longitude' => $row[1],
                    ]);
                }
            }
        }

        return redirect('/land-plot')->with('success', 'Land Plot updated successfully.');
    } catch (\Exception $e) {
        // Handle exceptions and redirect back with errors
        return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while updating the data.']);
    }
}

public function ApprReject(Request $request)
    {
        $attachments = null;
        if ($request->has('appr_rej_doc')) {
            $path = $request->file('appr_rej_doc')->store('land_plot_documents', 'public');
            $attachments = $path;
        } elseif ($request->has('stored_appr_rej_doc')) {
            $attachments = $request->stored_appr_rej_doc;
        } else {
            $attachments = null;
        }

        $update = LandPlot::find($request->appr_rej_land_id);
        $approveDocument = Helper::approveDocument($update->book_id, $update->id, $update->revision_number ,$request->appr_rej_remarks, $attachments, $update->approvalLevel, $request->appr_rej_status);
        $update->approvalLevel = $approveDocument['nextLevel'];
        $update->approvalStatus = $approveDocument['approvalStatus'];
        $update->appr_rej_recom_remark = $request->appr_rej_remarks ?? null;
        $update->appr_rej_doc = $attachments;
        $update->appr_rej_behalf_of = $request->appr_rej_behalf_of ? json_encode($request->appr_rej_behalf_of) : null;

        $update->save();
        $created_by = $update->landable_id;
        $creator = AuthUser::find($created_by);

        $user = Helper::getAuthenticatedUser()->id;
        $approver = Helper::userCheck()['user_type'];
        $approver= $approver::find($user);


        if ($request->appr_rej_status =='approve') {
            LandNotificationController::notifyLandPlotApproved($creator->authUser(),$update,$approver);
            return redirect("land-plot")->with(
                "success",
                "Approved Successfully!"
            );
        } else {
            LandNotificationController::notifyLandPlotReject($creator->authUser(),$update,$approver);

            return redirect("land-plot")->with(
                "success",
                "Rejected Successfully!"
            );
        }


    }
    public function amendment(Request $request, $id)
    {
        $land_id = LandPlot::find($id);
            if (!$land_id) {
                return response()->json(['data' => [], 'message' => "Land Plot not found.", 'status' => 404]);
            }

            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'LandPlot', 'relation_column' => ''],
            ];

            $a = Helper::documentAmendment($revisionData, $id);
       DB::beginTransaction();
        try {
            if ($a) {
                Helper::approveDocument($land_id->book_id, $land_id->id, $land_id->revision_number , 'Amendment', $request->file('attachment'), $land_id->approvalLevel, 'amendment');

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

}
