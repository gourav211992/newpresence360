<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use App\Http\Controllers\LandNotificationController;
use stdClass;

class LandLease extends Model
{
    use HasFactory,DefaultGroupCompanyOrg,Deletable;
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->document_status = $model->approvalStatus;
            $model->approval_level = $model->approvalLevel;
        });
    }




    protected $table = 'erp_land_leases';
    public $referencingRelationships = [
        'customer' => 'customer_id',
        'currency' => 'currency_id',
        'exchange'=>'exchage_rate'
    ];
    protected $guarded = ['id'];

    public static function createUpdateLease($request, $edit_id = 0)
    {
        $organization = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->first();

        if (!$organization) {
            throw new \Exception(message: 'Organization not found.');
        }


        $lease_get = LandLease::where('id', $edit_id)->first();
        $status = $request->status;
        $book_id =$lease_get->book_id?? $request->book_id;
        if ($status == ConstantHelper::SUBMITTED) {
            $status = Helper::checkApprovalRequired($book_id);
        }

        $userData = Helper::userCheck();

        $lease_get = LandLease::where('id', $edit_id)->first();

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


        $lease = LandLease::updateOrCreate([
            'id' => $edit_id
        ], [
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
            // 'type' => $organization->company_id, // Ensure this is correct
            'book_id' => $edit_id !== 0 ? $lease_get->book_id :$request->input('book_id'),
            'series_id' => $edit_id !== 0 ? $lease_get->book_id :$request->input('book_id'),
            'document_no' => $edit_id !== 0 ? $lease_get->document_no : $request->document_no,
            'document_date' => Carbon::parse($request->document_date)->format('Y-m-d'),
            'doc_number_type'=>$edit_id !== 0 ? $lease_get->doc_number_type :$request->input('doc_number_type'),
            'doc_reset_pattern'=>$edit_id !== 0 ? $lease_get->doc_reset_pattern :$request->input('doc_reset_pattern'),
            'doc_prefix'=>$edit_id !== 0 ? $lease_get->doc_prefix :$request->input('doc_prefix'),
            'doc_suffix'=>$edit_id !== 0 ? $lease_get->doc_suffix :$request->input('doc_suffix'),
            'doc_no'=>$edit_id !== 0 ? $lease_get->doc_no :$request->input('doc_no'),
            'land_id'=>$edit_id !== 0 ? $lease_get->land_id :$request->input('land'),
            'reference_no' => $request->reference_no,
            'customer_id' => $request->customer_id,
            'currency_id' => $request->currency_id,
            'exchage_rate' => $request->exchage_rate, // Fix typo
            'agreement_no' => $request->agreement_no,
            'lease_time' => $request->lease_time,
            'lease_start_date' => Carbon::parse($request->lease_start_date)->format('Y-m-d'),
            'lease_end_date' => Carbon::parse($request->lease_end_date)->format('Y-m-d'),
            'repayment_period_type' => $request->repayment_period_type,
            'repayment_period' => $request->repayment_period,
            'security_deposit' => $request->security_deposit,
            'deposit_refundable' => $request->deposit_refundable,
            'processing_fee' => $request->processing_fee,
            'lease_increment' => $request->lease_increment,
            'lease_increment_duration' => $request->lease_increment_duration,
            'grace_period' => $request->grace_period,
            'late_fee' => $request->late_fee,
            'late_fee_value' => $request->late_fee_value,
            'late_fee_duration' => $request->late_fee_duration,
            'sub_total_amount' => $request->sub_total_amount,
            'extra_charges' => $request->lease_other_charges,
            'tax_amount' => 0.00,
            'total_amount' => $request->total_amount,
            'remarks' => $request->remarks,
            'approvalStatus' => $status,
            'approvalLevel' => $edit_id !== 0 ? $lease_get->approvalLevel :1,
            'leaseable_id' => $userData['user_id'],
            'leaseable_type' => $userData['user_type'],
            'attachments' => json_encode($json),
            'otherextra_charges' => $request->lease_extra_charges ?? 0.00,
            'installment_amount' => $request->lease_installment_cost ?? 0.00,
            'extra_othercharges_json'=>$request->lease_extra_charges_json,
        'billing_address'=>$request->billing_address

        ]);

        $update = LandLease::find($lease->id);

            if ($status != ConstantHelper::DRAFT) {
                $bookId = $update->book_id;
                $docId = $update->id;
                $remarks = $update->remarks;
                $attachments = $request->file('attachments');
                $currentLevel = $update->approvalLevel;
                $revisionNumber = $update->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
                if($status==ConstantHelper::SUBMITTED){
                    //dd($update->approvelworkflow->count());

                    if ($update->approvelworkflow->count() > 0) { // Check if the relationship has records
                        foreach ($update->approvelworkflow as $approver) {
                            if ($approver->user) { // Check if the related user exists
                                $approver_user = $approver->user;
                                LandNotificationController::notifyLeaseCreation($approver_user, $update);
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




        return $lease;
    }


    public function series()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    public function schedule()
    {
        return $this->hasMany(LandLeaseScheduler::class, 'lease_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function exchange()
    {
        return $this->belongsTo(CurrencyExchange::class, 'exchage_rate');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function land()
    {
        return $this->belongsTo(LandParcel::class, 'land_id');
    }

    public function plots()
    {
        return $this->hasMany(LandLeasePlot::class, 'lease_id');
    }
    public function approvelworkflow()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'book_id');
    }

    public function plots_display()
    {
        $plots = $this -> plots() -> get();
        $displayPlots = '';
        foreach ($plots as $plotIndex => $plot) {
            $displayPlots .= ($plotIndex == 0 ? '' : ', ') .$plot ?-> plot ?-> plot_name;
        }
        return $displayPlots;
    }

    public function otherCharges()
    {
        return $this->hasMany(LandLeaseOtherCharges::class, 'lease_id');
    }

    public function address()
    {
        return $this->hasOne(LandLeaseAddress::class, 'lease_id');
    }

    public function document()
    {
        return $this->hasMany(LandLeaseDocument::class, 'lease_id');
    }
    public function actions()
    {
        return $this->hasMany(LandLeaseAction::class, 'source_id');
    }

    public function schedules()
    {
        return $this -> hasMany(LandLeaseScheduler::class, 'lease_id');
    }

    public function securityItem()
    {
        $itemDetail = new stdClass();
        $itemDetail -> id = 0;
        $itemDetail -> lease_id = $this -> id;
        $itemDetail -> installment_cost = ($this -> getAttribute('security_deposit') ? $this -> getAttribute('security_deposit') : 0) - ($this -> getAttribute('invoice_security_deposit') ? $this -> getAttribute('invoice_security_deposit') : 0);
        $itemDetail -> due_date = $this -> getAttribute('document_date');
        $itemDetail -> status = ConstantHelper::PENDING;
        $itemDetail -> type = ConstantHelper::SECURITY_DEPOSIT;
        $itemDetail -> header = $this;
        $itemDetail -> can_check = false;
        $itemDetail -> can_check_message = 'Security Item not found, Please update before selecting';
        $landParcelId = $this -> plots() -> first() ?-> land_parcel_id;
        $landParcel = LandParcel::find($landParcelId);
        if (isset($landParcel)) {
            $itemDetails = json_decode($landParcel -> service_item, true);
            $securityItem = array_filter($itemDetails, function ($leaseItem) {
                return $leaseItem["'servicetype'"] === 'security';
            });
            if ($securityItem && count($securityItem) > 0) {
                $securityItem = array_values($securityItem);
                $item = Item::where('item_code', $securityItem[0]["'servicecode'"]) -> where('type', ConstantHelper::SERVICE) -> first();
                if (isset($item)) {
                    $itemDetail -> can_check = true;
                    $itemDetail -> can_check_message = '';
                }
            }
        }
        return $itemDetail;
    }
}
