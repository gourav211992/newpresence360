<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LandLeaseHistory extends Model
{
    use HasFactory,DefaultGroupCompanyOrg,Deletable;

    protected $table = 'erp_land_leases_history';

    protected $fillable = [
        'source_id',
        'organization_id',
        'group_id',
        'company_id',
        'series_id',
        'document_no',
        'document_date',
        'reference_no',
        'customer_id',
        'currency_id',
        'exchage_rate',
        'billing_address',
        'agreement_no',
        'lease_time',
        'lease_start_date',
        'lease_end_date',
        'repayment_period_type',
        'repayment_period',
        'security_deposit',
        'deposit_refundable',
        'processing_fee',
        'lease_increment',
        'lease_increment_duration',
        'grace_period',
        'late_fee',
        'late_fee_value',
        'late_fee_duration',
        'extra_charges',
        'tax_amount',
        'total_amount',
        'remarks',
        'approvalStatus',
        'approvalLevel',
        'attachments',
        'sub_total_amount',
        'installment_amount',
        'otherextra_charges',
        'revision_number',
        'revision_date'
    ];


    public static function createUpdateLease($request, $edit_id = 0)
    {
        $organization = Organization::where('source_id', Helper::getAuthenticatedUser()->organization_id)->first();

        if (!$organization) {
            throw new \Exception(message: 'Organization not found.');
        }

        if ($edit_id == 0) {
            do {
                $document_no = Helper::reGenerateDocumentNumber($request->series_id);
                $existing_data = LandLease::where('document_no', $document_no)->first();
            } while ($existing_data !== null);
        }

        $status = $request->status == ConstantHelper::SUBMITTED
            ? Helper::checkApprovalRequired($request->series_id)
            : $request->status;

        $userData = Helper::userCheck();

        $lease_get = LandLease::where('source_id', $edit_id)->first();

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
            'source_id' => $edit_id
        ], [
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
            'user_id' => $organization->company_id, // Ensure this is correct
            'type' => $organization->company_id, // Ensure this is correct
            'series_id' => $request->series_id,
            'document_no' => $edit_id !== 0 ? $lease_get->document_no : $document_no,
            'document_date' => Carbon::parse($request->document_date)->format('Y-m-d'),
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
            'leaseable_id' => $userData['user_id'],
            'leaseable_type' => $userData['user_type'],
            'attachments' => json_encode($json),
            'otherextra_charges' => $request->lease_extra_charges ?? 0.00,
            'installment_amount' => $request->lease_installment_cost ?? 0.00,
        'billing_address'=>$request->billing_address
        ]);

        return $lease;
    }
    public $referencingRelationships = [
        'customer' => 'customer_id',
        'currency' => 'currency_id',
        'exchange'=>'exchage_rate',
        'lease'=>'source_id'
    ];

    public function series()
    {
        return $this->belongsTo(Book::class, 'series_id');
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
    public function source()
    {
        return $this->belongsTo(LandLease::class, 'source_id');
    }
    public function plots()
    {
        return $this->hasMany(LandLeasePlot::class, 'lease_id');
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
}
