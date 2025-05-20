<?php

namespace App\Http\Controllers\API\TransporterRequest;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\AuthUser;
use App\Models\Employee;
use App\Models\ErpStore;
use App\Models\ErpTransporterRequest;
use App\Models\ErpTransporterRequestBid;
use App\Models\ErpTransporterRequestLocation;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class TransporterRequestApiController extends Controller
{
    //
    public function create(Request $request){
        //requirments user_id,user_type, organization_id ,series,loading time, bid end time ,vehicle type , weight qty and code transporters(optional),remarks,pick up location id/name , drop off complete location 
        // Validate the request
        $validator = Validator::make($request->all(), [
            'auth_user_id' => 'required',
            'organization_id' => 'required',
            'uom_id' => 'required|exists:erp_units,id',
            'vehicle_type' => 'required|string',
            'weight' => 'required|numeric|min:1',
            'book_id' => 'required|integer',
            'book_code' => 'required|string',
            'loading_date' => 'required|date',
            'bid_end_date' => 'required|date',
            'transporter_ids' => 'nullable|array',
            'transporter_ids.*' => 'integer|exists:erp_vendors,id',
            'location_pick_up' => 'required|array|min:1',
            'location_pick_up.*' => 'integer|exists:erp_stores,id',
            'location_drop' => 'required|array|min:1',
            'd_address' => 'required|array|min:1',
            'd_pin_code' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $user = AuthUser::find($request->auth_user_id);
            // dd($user);
            if(!$user){
                return response()->json([
                    'message' => 'Invalid User',
                ],500);
            }
            $authUser = AuthUser::find(5);
            Auth::guard('web')->login(User::find(2));
            auth() -> user() -> authenticable_type = $authUser->authenticable_type;
            auth() -> user() -> auth_user_id = $authUser->id;
            $organization = Organization::find($request->organization_id);
            $organizationId = $organization?->id ?? null;
            $groupId = $organization?->group_id ?? null;
            $companyId = $organization?->company_id ?? null;
            $document_number = Helper::generateDocumentNumberNew($request->book_id,Carbon::now()->toString());
            $uom = Unit::find($request->uom_id);
            if(!$uom){
                return response()->json([
                    'message' => "UOM Not Found",
                ],404);
            }
            
            // Create Transporter Request
            $transporter_ids = $request->transporter_ids ?? null;
            $tr = ErpTransporterRequest::create([
                'organization_id' => $organizationId,
                'group_id' => $groupId,
                'company_id' => $companyId,
                'book_id' => $request->book_id,
                'book_code' => $request->book_code,
                'document_number' => $document_number['document_number'],
                'doc_number_type' => $document_number['type'],
                'doc_reset_pattern' => $document_number['reset_pattern'],
                'doc_prefix' => $document_number['prefix'],
                'doc_suffix' => $document_number['suffix'],
                'doc_no' => $document_number['doc_no'],
                'document_date' => Carbon::now(),
                'loading_date_time' => $request->loading_date,
                'document_status' => ConstantHelper::APPROVAL_NOT_REQUIRED,
                'approval_level' => 1,
                'remarks' => $request?->remarks,
                'vehicle_type' => $request->vehicle_type,
                'total_weight' => $request->weight,
                'uom_id' => $uom->id,
                'uom_code' => $uom->name,
                'bid_end' => $request->bid_end_date,
                'transporter_ids' => $transporter_ids,
            ]);

            // Process Pickup Locations
            foreach ($request->location_pick_up as $i => $locationId) {
                // dd($locationId);
                $pickUpLocation = ErpStore::findOrFail($locationId);
                $address = $pickUpLocation->address;
                $locationAddress = $tr->location_address_details()->create([
                    'address' => $address -> address,
                    'country_id' => $address->country_id,
                    'state_id' => $address->state_id,
                    'city_id' => $address->city_id,
                    'pincode' => $address->pincode,
                ]);
                ErpTransporterRequestLocation::create([
                    'transporter_request_id' => $tr->id,
                    'address_id' => $locationAddress->id,
                    'location_id' => $pickUpLocation->id,
                    'location_name' => $pickUpLocation->store_code,
                    'location_type' => "pick_up",
                ]);
            }
            // dd($request);
            for ($i=0; $i <count($request->location_drop) ; $i++) { 
                $locationAddress = $tr->location_address_details()->create([
                    'address' => $request?->d_address[$i],
                    'country_id' => $request->d_country_id[$i]??null,
                    'state_id' => $request->d_state_id[$i]??null,
                    'city_id' => $request->d_city_id[$i]??null,
                    'pincode' => $request->d_pin_code[$i]??null,
                ]);
                $transporter_location = ErpTransporterRequestLocation::create(
                    [
                        'transporter_request_id' => $tr->id,
                        'address_id' => $locationAddress->id,
                        'location_name' => $request?->location_drop[$i],
                        'location_type' =>"drop_off" ,
                    ]
                );
            }

            if ($transporter_ids) {
                $vendors = Vendor::whereIn('id', $transporter_ids)->get(); // Keep as a collection
            }
            else{
                $vendors = Vendor::where('company_id',$companyId)->get();
            }
            foreach ($vendors as $vendor) { 
                $sendTo = $vendor->email;
                $title = "New Transporter Request";
                $bidLink = route('supplier.transporter.index',[$vendor->id]); // Generate route in PHP
                $name = $vendor->company_name;
                $description = <<<HTML
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                    <tr>
                        <td align="left" style="padding: 10px 0;">
                            <h2 style="color: #333;">New Transporter Request – Invitation to Bid</h2>
                            <p>Dear {$name},</p>
                            <p>A new transporter request has been created for delivery. As a valued logistics partner, we invite you to place your bid for it.</p>
                            <p>Kindly submit your bid in a timely manner to be considered for this opportunity.</p>
                            <p style="text-align: center;">
                                <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                    Place Your Bid
                                </a>
                            </p>
                            <p>If you have any questions or require further details, please do not hesitate to contact us.</p>
                        </td>
                    </tr>
                </table>
                HTML;
                self::sendMail($vendor,$title,$description);
            }

            DB::commit();
            return response()->json(['message' => 'Transporter request created successfully', 'data' => $tr], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage().$e->getLine().$e->getFile()], 500);
        }

    }
    
    // public function reopen(Request $request){
    //     // requirments tr_id
    //     $validator = Validator::make($request->all(), [
    //         'auth_user_id' => 'required',
    //         'organization_id' => 'required',
    //         'tr_id' => "required",
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         DB::beginTransaction();
        
    //         $authUser = AuthUser::find($request->auth_user_id);
    //             if(!$authUser){
    //                 return response()->json([
    //                     'message' => 'Invalid User',
    //                 ],500);
    //             }
    //         Auth::guard('web')->login(User::find(2));
    //         auth() -> user() -> authenticable_type = $authUser->authenticable_type;
    //         auth() -> user() -> auth_user_id = $authUser->id;
    //         $tr= ErpTransporterRequest::find($request->tr_id);
    //         if($tr){

    //             if(in_array($tr->document_status,[ConstantHelper::CLOSED])){
    //                 $tr->document_status = ConstantHelper::DRAFT;
    //                 $tr->save();
    //                 $message = 'Request Reopened Successfully';
    //                 $status = 200;
    //             }
    //             else{
    //                 $message = "Request Not Closed";
    //                 $status = 500;
    //             }
    //         }
    //         else{
    //             $message = "Request Not Found";
    //             $status = 500;
    //         }
    //         if($status =200){
    //             $transporter_ids = json_decode($tr->transporter_ids);
    //             if ($transporter_ids) {
    //                 $vendors = Vendor::whereIn('id', $transporter_ids)->get();
    //             }
    //             else{
    //                 $vendors = Vendor::withDefaultGroupCompanyOrg()->get();
    //             }
    //             foreach ($vendors as $vendor) { 
    //                 $sendTo = $vendor->email;
    //                 $title = "Transporter Request Reopened";
    //                 $bidLink = route('supplier.transporter.index',[$vendor->id]);
    //                 $name = $vendor->company_name;
    //                 $bid_name = $tr->document_number;
    //                 $description = <<<HTML
    //                 <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
    //                     <tr>
    //                         <td align="left" style="padding: 10px 0;">
    //                             <h2 style="color: #333;">Bid Reopened – Invitation to Bid Again</h2>
    //                             <p>Dear {$name},</p>
    //                             <p>We would like to inform you that the bid <strong>{$bid_name}</strong> has been reopened.</p>
    //                             <p>As a valued logistics partner, we invite you to review the bid details and place your bid again.</p>
    //                             <p>Kindly submit your bid in a timely manner to be considered for this opportunity.</p>
    //                             <p style="text-align: center;">
    //                                 <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
    //                                     Place Your Bid
    //                                 </a>
    //                             </p>
    //                             <p>If you have any questions or require further details, please do not hesitate to contact us.</p>
    //                         </td>
    //                     </tr>
    //                 </table>
    //                 HTML;
    //                 self::sendMail($vendor,$title,$description);
    //             }
    //             $tr->save();
    //         }
    //         $remarks = $request->remarks??"";
    //         $approveDocument = Helper::approveDocument($tr->book_id, $tr->id, 0, $remarks, [], $tr->approval_level, 'bid-reopened' , 0,get_class($tr));
    //         DB::commit();
    //         return response()->json(['message'=>$message],$status);
    //     }
    //     catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage().$e->getLine().$e->getFile()], 500);
    //     }
    // }
    public function close(Request $request){
        $validator = Validator::make($request->all(), [
            'auth_user_id' => 'required',
            'organization_id' => 'required',
            'tr_id' => "required",
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $authUser = AuthUser::find($request->auth_user_id);
                if(!$authUser){
                    return response()->json([
                        'message' => 'Invalid User',
                    ],500);
                }
            Auth::guard('web')->login(User::find($authUser->authenticable_id));
            auth() -> user() -> authenticable_type = $authUser->authenticable_type;
            auth() -> user() -> auth_user_id = $authUser->id;
            $tr= ErpTransporterRequest::withDefaultGroupCompanyOrg()->find($request->tr_id);
            if($tr){
                if(!in_array($tr->document_status,[ConstantHelper::CLOSED])){
                    $tr->document_status = ConstantHelper::CLOSED;
                    $tr->save();
                    $message = 'Bid Closed Successfully';
                    $status = 200;
                    $remarks = $request->remarks ?? null;
                    $approveDocument = Helper::approveDocument($tr->book_id, $tr->id, 0, $remarks, [], $tr->approval_level, 'bid-closed' , 0,get_class($tr));
                }
                else{
                    $message = "Bid Already Closed";
                    $status = 500;
                }
            }
            else{
                $message = "Bid Not Found";
                $status = 500;
            }

            DB::commit();

        return response()->json([
            'message' => $message,
            'data' => $tr ?? null
            ], $status);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage().$e->getLine().$e->getFile()], 500);
        }
    }

    public function shortlist(Request $request){
        // requirments tr_id and vendor_id/bid_id
        $validator = Validator::make($request->all(), [
            'auth_user_id' => 'required',
            'organization_id' => 'required',
            'tr_id' => "required",
            'bid_id' => "required",
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }
        try {
            DB::beginTransaction();
            
            $authUser = AuthUser::find($request->auth_user_id);
                if(!$authUser){
                    return response()->json([
                        'message' => 'Invalid User',
                    ],500);
                }
            Auth::guard('web')->login(User::find(2));
            auth() -> user() -> authenticable_type = $authUser->authenticable_type;
            auth() -> user() -> auth_user_id = $authUser->id;
            $tr= ErpTransporterRequest::withDefaultGroupCompanyOrg()->whereNotIn('document_status',[ConstantHelper::COMPLETED,ConstantHelper::CLOSED])->find($request->tr_id);
            $bid = ErpTransporterRequestBid::find($request->bid_id);
            if (!$tr) {
                return response()->json(['message' => "Transporter request not found."], 500);
            } else {
                if (!$bid) {
                    return response()->json(['message' => "Bid not found."], 500);
                } else {
                    if ($tr->id != $bid->transporter_request_id) {
                        return response()->json(['message' => "Invalid Bid-Request Pair."], 500);
                    }
                }
            }
            $tr->selected_bid_id = $request->bid_id;
            // Update all bids' status for the given transporter_request_id
            $bid_details = ErpTransporterRequestBid::find($request->bid_id);
            if($bid_details && $bid_details->bid_status==ConstantHelper::SHORTLISTED){
                return response()->json(['message'=>'Bid Already Shortlisted'],200);
            }
            ErpTransporterRequestBid::where('transporter_request_id', $tr->id)->whereNotIn('bid_status',["cancelled"])
                ->update(['bid_status' => ConstantHelper::SUBMITTED]);
        
            // Fetch the specific bid that needs to be shortlisted
            if ($bid_details) { // Ensure bid exists before modifying
                $bid_details->update(['bid_status' => ConstantHelper::SHORTLISTED]);
                $bookId = $tr->book_id;
                $docId = $tr->id;
                $remarks = $tr->remarks;
                $attachments = $request?->file('attachment')??[];
                $currentLevel = $tr->approval_level;
                $revisionNumber = $tr->revision_number ?? 0;
                $actionType = 'shortlist'; // Approve // reject // submit
                $modelName = get_class($tr);
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
            }
            $transporter_ids = json_decode($tr->transporter_ids);
            if ($transporter_ids) {
                $vendors = Vendor::withDefaultGroupCompanyOrg()->whereIn('id', $transporter_ids)->get(); // Keep as a collection
            }
            else{
                $vendors = Vendor::withDefaultGroupCompanyOrg()->get();
            }
            foreach ($vendors as $vendor) { 
                $sendTo = $vendor->email;
                $title = "New Transporter Request";
                $bidLink = route('supplier.transporter.index',[$vendor->id]); // Generate route in PHP
                $name = $vendor->company_name;
                $bid_name = $tr->document_number;
                $description = <<<HTML
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                    <tr>
                        <td align="left" style="padding: 10px 0;">
                            <h2 style="color: #333;">Bid Shortlisting Notification – Vehicle Details Required</h2>
                            <p>Dear {$name},</p>
                            <p>We are pleased to inform you that you have been shortlisted for the bid <strong>{$bid_name}</strong>.</p>
                            <p>As the next step, we kindly request you to provide us with the necessary vehicle details to proceed further.</p>
                            <p>Timely submission of this information is essential to finalize the process.</p>
                            <p style="text-align: center;">
                                <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                    Submit Vehicle Details
                                </a>
                            </p>
                            <p>If you have any questions or require further clarification, please feel free to contact us.</p>
                            <p>We appreciate your cooperation and look forward to working together.</p>
                        </td>
                    </tr>
                </table>
                HTML;                    
                self::sendMail($vendor,$title,$description);
            }
            $bid_details->save(); // Save the shortlisted bid
            $tr->document_status = ConstantHelper::SHORTLISTED;
            $tr->save();
            DB::commit();
            return response()->json(['message'=>"Shortlisted Successfully","bid"=>$bid_details]);
            
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage().$e->getLine().$e->getFile()], 500);
        }
    }
    public function get_request_list(Request $request){
        // requirments tr_id
        $validator = Validator::make($request->all(), [
            'auth_user_id' => 'required',
            'organization_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            
            $authUser = AuthUser::find($request->auth_user_id);
                // dd($user);
                if(!$authUser){
                    return response()->json([
                        'message' => 'Invalid User',
                    ],500);
                }
            Auth::guard('web')->login(User::find(2));
            auth() -> user() -> authenticable_type = $authUser->authenticable_type;
            auth() -> user() -> auth_user_id = $authUser->id;
            $transporter_requests= ErpTransporterRequest::withDefaultGroupCompanyOrg()->where('created_by',$authUser->id)->whereNotIn('document_status',[ConstantHelper::COMPLETED,ConstantHelper::CLOSED])->get();
            DB::commit();
            return response()->json([
                'message' => 'Data Found',
                'data' => $transporter_requests,
            ]);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage().$e->getLine().$e->getFile()], 500);
        }
    }

    public function get_bid_details(Request $request){
        $validator = Validator::make($request->all(), [
            'auth_user_id' => 'required',
            'organization_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            
            $authUser = AuthUser::find($request->auth_user_id);
                // dd($user);
                if(!$authUser){
                    return response()->json([
                        'message' => 'Invalid User',
                    ],500);
                }
            Auth::guard('web')->login(User::find(2));
            auth() -> user() -> authenticable_type = $authUser->authenticable_type;
            auth() -> user() -> auth_user_id = $authUser->id;

            $tr= ErpTransporterRequest::withDefaultGroupCompanyOrg()->find($request->tr_id);
            if($tr){
                $bids=[];
                if($request->only_shortlisted){
                    $bids[]=$tr?->bid?->toArray();
                }
                else{
                    $bids = $tr->bids;
                }
                if(count($bids)){
                    return response()->json([
                        'message' => "data found",
                        'Request'=> $tr,
                        "Bids"=> $bids,
                    ],200);
                }
                else{
                    return response()->json(['message'=>'request found but no bids available',
                    'Request'=>$tr],200);
                }
            }
            else{
                return response()->json(['message'=>'Request Not Found'],500);
            }
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage().$e->getLine().$e->getFile()], 500);
        }
    }
    public function sendMail($receiver, $title, $description)
    {
        if (!$receiver || !isset($receiver->email)) {
            return "Error: Receiver details are missing or invalid.";
        }
        dispatch(new SendEmailJob($receiver, $title, $description));
        return "Success: Email job dispatched!";
    }
}
