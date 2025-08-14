<?php

namespace App\Http\Controllers\Supplier;

use App\Jobs\SendEmailJob;
use App\Models\ErpTransporterRequest;
use App\Models\ErpTransporterRequestBid;
use App\Models\ErpTransporterRequestLocation;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class ErpTransportersController extends Controller
{
    //
    public function index(Request $request,$vendor_id = null)
    {
        if ($vendor_id) {
            Cookie::queue('vendor_id', $vendor_id);
        }
        $vendorid = $request->cookie('vendor_id');
        $typeName = "Transporter Bids";
        $bids = ErpTransporterRequest::
            with(['bids' => function ($query) use ($vendorid) {
                $query->where('transporter_id', (int) $vendorid);
            }])
            ->where(function ($query) use ($vendorid) {
                $query->whereJsonContains('transporter_ids', (string) $vendorid)
                    ->orWhereJsonContains('transporter_ids', (int) $vendorid) // Ensure it works for integer storage
                    ->orWhereNull('transporter_ids');
            })
            ->orderByDesc('id')
            ->get();
        $PastBid = [];
        $liveBids = [];
        foreach($bids as $bid){
            $bidStatus = $bid?->bids->first()?->bid_status;
            if($bid->document_status == ConstantHelper::DRAFT){
                continue;
            }
            elseif ($bidStatus == ConstantHelper::SUBMITTED && in_array($bid->document_status, [ConstantHelper::CONFIRMED, ConstantHelper::SHORTLISTED, ConstantHelper::CLOSED]))
            {
                // $showBids = false;
                $PastBid[]=$bid;
            }
            else if(in_array($bid->document_status, [ConstantHelper::APPROVAL_NOT_REQUIRED, ConstantHelper::APPROVAL]))
            {
                // $showBids = true;
                $liveBids[] = $bid;
            } else if((in_array($bidStatus, [ConstantHelper::SHORTLISTED, ConstantHelper::CONFIRMED, ConstantHelper::CANCELLED]) && $bid->document_status != ConstantHelper::CLOSED)) {
                $liveBids[] = $bid;

                // $showBids = true; // Show the bid
            }
            else{
                $PastBid[]=$bid;
            // $showBids = false; // Hide the bid
            }
            if(Carbon::parse($bid->bid_end)->setTimezone(config('app.timezone'))->isPast() && in_array($bid->document_status,[ConstantHelper::APPROVED,ConstantHelper::APPROVAL_NOT_REQUIRED])){
                $bid->document_status= ConstantHelper::COMPLETED;
                $bid->save();
            }
            if(Carbon::parse($bid->loading_date_time)->setTimezone(config('app.timezone'))->isPast()){
                $bid->document_status = ConstantHelper::CLOSED;
                $bid->save();
            }
        }
        return view('transporters.index', [
            'typeName' => $typeName,
            'bids' => $liveBids,
            'past_bids' => $PastBid,
         ]);

    }
    public function submit_bid(Request $request){
        // dd(Carbon::parse($request->bid_end_date)->lt(Carbon::now()));
        $vendor_id = $request->cookie('vendor_id');
        if (!Carbon::parse($request->bid_end_date)->setTimezone(config('app.timezone'))->isPast()) {

            ErpTransporterRequestBid::create([
                'transporter_request_id'=>$request->bid_id,
                'transporter_id' => $vendor_id,
                'bid_price' => $request->bid_price,
                'bid_status' => ConstantHelper::SUBMITTED,
            ]);
            $tr = ErpTransporterRequest::find($request->bid_id);
            $vendor = Vendor::find($vendor_id);
            $sendTo = $vendor->email;
            $title = "Bid Submitted Successfully";
            $bid_name = $tr->document_number;
            $bidLink = route('supplier.transporter.index',[$vendor->id]); // Generate route in PHP
            $name = $vendor->company_name;
            $description = <<<HTML
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                <tr>
                    <td align="left" style="padding: 10px 0;">
                        <h2 style="color: #333;">Bid Submitted Successfully</h2>
                        <p>Dear {$name},</p>
                        <p>We are pleased to inform you that your bid for <strong>{$bid_name}</strong> has been successfully submitted.</p>
                        <p>Our team will carefully review all submissions, and shortlisted vendor(s) will be notified accordingly.</p>
                        <p>We appreciate your participation and patience during this process.</p>
                        <p>If you have any questions or require further clarification, please do not hesitate to contact us.</p>
                        <p>Thank you for your continued partnership, and we look forward to working together.</p>
                    </td>
                </tr>
            </table>
            HTML;

            self::sendMail($vendor,$title,$description);
            $creator = $tr?->createdBy?->authUser();
            if ($creator) {
                $sendToCreator = $creator->email;
                $titleCreator = "New Bid Submitted on Your Request";
                $nameCreator = $creator->name;
                $bidLink = route('transporter.edit',[$tr->id]);
                $descriptionCreator = <<<HTML
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                    <tr>
                        <td align="left" style="padding: 10px 0;">
                            <h2 style="color: #333;">New Bid Submitted</h2>
                            <p>Dear {$nameCreator},</p>
                            <p>We would like to inform you that a bid has been submitted for your transporter request <strong>{$bid_name}</strong>.</p>
                            <p>You can review the submitted bids and proceed with the evaluation.</p>
                            <p>Click the link below to view the bid details:</p>
                            <p style="text-align: center;">
                                <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                    View Submitted Bids
                                </a>
                            </p>
                            <p>If you have any questions or need further assistance, please feel free to reach out.</p>
                            <p>Thank you.</p>
                        </td>
                    </tr>
                </table>
                HTML;
            
                self::sendMail($creator, $titleCreator, $descriptionCreator);
            }
            return response()->json([
                'message' => 'Bid Submitted Successfully',
                'success' => "",
            ], 200);
        }
        else{
            return;
        }
    }
    public function change_bid(Request $request) {
        $vendor_id = $request->cookie('vendor_id');
    
        // Check if bid end date is still valid
        if (!Carbon::parse($request->bid_end_date)->setTimezone(config('app.timezone'))->isPast()) {
            
            // Update the bid price
            $bidUpdated = ErpTransporterRequestBid::where('transporter_request_id', $request->bid_id)
                ->where('transporter_id', $vendor_id)
                ->update(['bid_price' => (float) $request->bid_price]); // Ensure correct data type
            
            if ($bidUpdated) {
                // Fetch Transporter Request and Vendor Details
                $tr = ErpTransporterRequest::find($request->bid_id);
                $vendor = Vendor::find($vendor_id);
                
                if ($tr && $vendor) {
                    $bid_name = $tr->document_number;
                    $vendorName = $vendor->company_name;
                    $bidLink = route('supplier.transporter.index', [$vendor->id]);
                    $editBidLink = route('transporter.edit', [$tr->id]);
                    
                    // Send email to vendor (confirmation of bid update)
                    $titleVendor = "Bid Updated Successfully";
                    $descriptionVendor = <<<HTML
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                        <tr>
                            <td align="left" style="padding: 10px 0;">
                                <h2 style="color: #333;">Bid Updated Successfully</h2>
                                <p>Dear {$vendorName},</p>
                                <p>We would like to confirm that your bid for <strong>{$bid_name}</strong> has been successfully updated.</p>
                                <p>Our team will review all bids, and shortlisted vendors will be notified accordingly.</p>
                                <p>If you have any questions, please do not hesitate to contact us.</p>
                                <p>Thank you for your participation.</p>
                                <p>Click the link below to view the bid details:</p>
                                <p style="text-align: center;">
                                    <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                        View Bid Details
                                    </a>
                                </p>
                            </td>
                        </tr>
                    </table>
                    HTML;
    
                    self::sendMail($vendor, $titleVendor, $descriptionVendor);
    
                    // Notify the creator about the bid modification
                    $creator = $tr->createdBy->authUser() ?? null;
    
                    if ($creator) {
                        $sendToCreator = $creator->email;
                        $titleCreator = "Bid Modification Notification";
                        $nameCreator = $creator->name;
    
                        $descriptionCreator = <<<HTML
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                            <tr>
                                <td align="left" style="padding: 10px 0;">
                                    <h2 style="color: #333;">Bid Modification Notification</h2>
                                    <p>Dear {$nameCreator},</p>
                                    <p>We would like to inform you that the vendor <strong>{$vendorName}</strong> has updated their previously submitted bid for <strong>{$bid_name}</strong>.</p>
                                    <p>You may review the updated bid details by clicking the link below:</p>
                                    <p style="text-align: center;">
                                        <a href="{$editBidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                            Review Updated Bid
                                        </a>
                                    </p>
                                    <p>If you have any questions or require further clarification, please do not hesitate to contact us.</p>
                                    <p>Thank you.</p>
                                </td>
                            </tr>
                        </table>
                        HTML;
    
                        self::sendMail($creator, $titleCreator, $descriptionCreator);
                    }
                }
                return response()->json([
                    'message' => 'Bid Price Changed Successfully'
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Bid modification deadline has passed.',
                'error' => true
            ], 400);
        }
    }
    

    public function get_locations(Request $request)
    {
        $loc = json_decode($request->location_ids);
        $location_data=[];
        foreach($loc as $id){
            $location = ErpTransporterRequestLocation::with(['address'])->find($id);
            $location_name = $location->location_name;
            $location_address = $location->address->getDisplayAddressAttribute(); 
            $location_data[]=[$location_name,$location_address];
            
        }
        return response()->json([
            'data' => $location_data
        ]);
    }
    public function submit_vehicle(Request $request)
    {
        $vendor_id = $request->cookie('vendor_id');
        $actionType = $request->action_type;
        $bid = ErpTransporterRequestBid::where('transporter_request_id', $request->bid_id)->where('transporter_id', $vendor_id)->first();
        $bid->vehicle_number = $request->vehicle_no;
        $bid->driver_name = $request->driver_name;
        $bid->driver_contact_no = $request->driver_contact;
        $bid->bid_status = ConstantHelper::CONFIRMED;
        $bid->transporter_remarks = $request->remarks;
        $bid->save();
        $tr = ErpTransporterRequest::find($bid->transporter_request_id);
        $tr->document_status = ConstantHelper::CONFIRMED;
        $tr->save();
        
        if($actionType == 'update'){
            $status = "updated";
        }
        else{
            $status = "added";
        }
        $approveDocument = Helper::approveDocument($tr->book_id, $tr->id, 0, $request->remarks, [], $tr->approval_level, "vehicle ".$status , 0,get_class($tr));
        $vendor = Vendor::find($vendor_id);
        $sendTo = $vendor->email;
        $title = ucfirst($status);
        $bidLink = route('supplier.transporter.index',[$vendor->id]);
        $bid_name = $tr->document_number;
        $name = $vendor->company_name;
        $description = <<<HTML
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
            <tr>
                <td align="left" style="padding: 10px 0;">
                    <h2 style="color: #333;">Vehicle Details {ucfirst($status)} – Bid Notification</h2>
                    <p>Dear {$name},</p>
                    <p>We would like to inform you that vehicle details have been {$status} for the bid <strong>{$bid_name}</strong>.</p>
                    <p>Please review the updated details to ensure your bid reflects the latest information.</p>
                    <p style="text-align: center;">
                        <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                            View Bid Details
                        </a>
                    </p>
                    <p>If you have any questions or require further details, please do not hesitate to contact us.</p>
                </td>
            </tr>
        </table>
        HTML;
        self::sendMail($vendor,$title,$description);
        $creator =$tr->createdBy->authUser();
        $vendor = Vendor::find($vendor_id);

        if ($creator && $vendor) {
            $sendToCreator = $creator->email;
            $titleCreator = "Bid Details Submitted";
            $nameCreator = $creator->name;
            $vendorName = $vendor->company_name;
            $bid_name = $tr->document_number;
            $bidLink = route('transporter.edit', [$tr->id]);

            $descriptionCreator = <<<HTML
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                <tr>
                    <td align="left" style="padding: 10px 0;">
                        <h2 style="color: #333;">Vehicle Details {ucfirst($status)} – Bid Notification</h2>
                        <p>Dear {$nameCreator},</p>
                        <p>We would like to inform you that the vendor <strong>{$vendorName}</strong> has {$status} their vehicle Details for <strong>{$bid_name}</strong>.</p>
                        <p>Please review the updated details to ensure your bid reflects the latest information.</p>
                        <p style="text-align: center;">
                            <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                View Bid Details
                            </a>
                        </p>
                        <p>If you have any questions or require further details, please do not hesitate to contact us.</p>
                    </td>
                </tr>
            </table>
            HTML;

            self::sendMail($creator, $titleCreator, $descriptionCreator);
        }
        return response()->json([
            'message' =>  ucfirst($status)." successfully",
        ]);
    }
    public function cancel_trip(Request $request)
    {
        $vendor_id = $request->cookie('vendor_id');
        $bid = ErpTransporterRequestBid::where('transporter_request_id', $request->bid_id)->where('transporter_id', $vendor_id)->first();
        $bid->bid_status = ConstantHelper::CANCELLED;
        $bid->save();
        $tr = ErpTransporterRequest::find($bid->transporter_request_id);
        $tr->document_status = ConstantHelper::APPROVAL_NOT_REQUIRED;
        $tr->selected_bid_id = null;
        $tr->save();

        $approveDocument = Helper::approveDocument($tr->book_id, $tr->id, 0, $request->remarks, [], $tr->approval_level, 'Cancelled' , 0,get_class($tr));
        $vendor = Vendor::find($vendor_id);
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
        $creator =$tr->createdBy->authUser();
        $vendor = Vendor::find($vendor_id);

        if ($creator && $vendor) {
            $sendToCreator = $creator->email;
            $titleCreator = "Bid Details Submitted";
            $nameCreator = $creator->name;
            $vendorName = $vendor->company_name;
            $bid_name = $tr->document_number;
            $bidLink = route('transporter.edit', [$tr->id]);

            $descriptionCreator = <<<HTML
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                <tr>
                    <td align="left" style="padding: 10px 0;">
                        <h2 style="color: #333;">Bid Canceled Notification</h2>
                        <p>Dear {$nameCreator},</p>
                        <p>We would like to inform you that the vendor <strong>{$vendorName}</strong> has canceled their previously submitted bid for <strong>{$bid_name}</strong>.</p>
                        <p>You may review the bid status by clicking the link below:</p>
                        <p style="text-align: center;">
                            <a href="{$bidLink}" target="_blank" style="background-color: #ff0000; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                Review Bid Status
                            </a>
                        </p>
                        <p>If you have any questions or require further clarification, please do not hesitate to contact us.</p>
                        <p>Thank you.</p>
                    </td>
                </tr>
            </table>
            HTML;

            self::sendMail($creator, $titleCreator, $descriptionCreator);
        }
        return response()->json([
            'message' =>  "Trip cancelled successfully",
        ]);
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
