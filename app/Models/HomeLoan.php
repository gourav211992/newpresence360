<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Carbon\Carbon;

class HomeLoan extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;
    protected $table = 'erp_home_loans';



    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->document_status = $model->approvalStatus;
            $model->approval_level = $model->approvalLevel;
        });
    }

    public function settlement()
    {
        return $this->hasOne(LoanSettlement::class, 'home_loan_id');
    }


    public function series()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    public function addresses()
    {
        return $this->hasOne(LoanAddress::class, 'home_loan_id');
    }

    public function employerDetails()
    {
        return $this->hasOne(LoanEmployerDetail::class, 'home_loan_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(LoanBankAccount::class, 'home_loan_id');
    }

    public function disbursalLoan()
    {
        return $this->hasMany(DisbursalLoan::class, 'home_loan_id');
    }
    public function loanDisbursements()
    {
        return $this->hasMany(LoanDisbursement::class, 'home_loan_id');
    }
    public function loanIncomes()
    {
        return $this->hasOne(LoanIncome::class, 'home_loan_id');
    }

    public function otherDetails()
    {
        return $this->hasOne(LoanOtherDetail::class, 'home_loan_id');
    }

    public function loanOtherGuarantors()
    {
        return $this->hasOne(LoanOtherGuarantor::class, 'home_loan_id');
    }

    public function proposedLoans()
    {
        return $this->hasOne(LoanProposedLoan::class, 'home_loan_id');
    }

    public function guarantorCoApplicants()
    {
        return $this->hasOne(LoanGuarantorCoApplicant::class, 'home_loan_id');
    }

    public function loanGuarApplicant()
    {
        return $this->hasOne(LoanGuarApplicant::class, 'home_loan_id');
    }
    public function loanAppraisal()
    {
        return $this->hasOne(ErpLoanAppraisal::class, 'loan_id')->withDefault('null');
    }
    public function loanApproval()
    {
        return $this->hasOne(LoanApproval::class, 'loan_application_id');
    }
    public function loanAssessment()
    {
        return $this->hasOne(LoanAssessment::class, 'loan_application_id');
    }
    public function loanSanctLetter()
    {
        return $this->hasOne(LoanSanctionLetter::class, 'loan_application_id');
    }
    public function loanLegalDoc()
    {
        return $this->hasOne(LoanLegalDoc::class, 'loan_application_id');
    }

    public function loanLegalDocs()
    {
        return $this->hasMany(LoanLegalDoc::class, 'loan_application_id');
    }
    public function loanProcessFee()
    {
        return $this->hasOne(LoanProcessFee::class, 'loan_application_id');
    }
    public function bankSecurity()
    {
        return $this->hasOne(VehicleBankSecurity::class, 'vehicle_id');
    }

    public function dataVehicle()
    {
        return $this->hasMany(VehicleLoan::class, 'vehicle_id');
    }

    public function vehicleScheme()
    {
        return $this->hasOne(LoanVehicleSchemeCost::class, 'vehicle_id');
    }

    public function financeSecurity()
    {
        return $this->hasOne(LoanFinanceLoanSecurity::class, 'vehicle_id');
    }

    public function netWorth()
    {
        return $this->hasMany(LoanGuarantorParty::class, 'vehicle_id');
    }

    public function guarantorAddress()
    {
        return $this->hasMany(LoanGuarantorPartyAddress::class, 'vehicle_id');
    }

    public function vehicleDocuments()
    {
        return $this->hasOne(LoanVehicleDocument::class, 'vehicle_id');
    }

    public function documents()
    {
        return $this->hasOne(LoanDocument::class, 'home_loan_id');
    }

    public function termLoanAddress()
    {
        return $this->hasOne(TermLoanAddress::class, 'term_loan_id');
    }

    public function termLoanPromoter()
    {
        return $this->hasMany(TermLoanPromoter::class, 'term_loan_id');
    }

    public function constitutions()
    {
        return $this->hasOne(TermLoanConstitution::class, 'term_loan_id');
    }

    public function meanFinance()
    {
        return $this->hasOne(TermLoanFinanceMean::class, 'term_loan_id');
    }

    public function termLoanNetWorth()
    {
        return $this->hasOne(TermLoanNetWorth::class, 'term_loan_id');
    }

    public function termLoanDocument()
    {
        return $this->hasOne(TermLoanDocument::class, 'term_loan_id');
    }

    public function loanDisbursement()
    {
        return $this->hasMany(LoanDisbursement::class, 'home_loan_id');
    }
    public function approvelworkflow()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'book_id', 'series');
    }
    public function recoveryLoan()
    {
        return $this->hasMany(RecoveryLoan::class, 'home_loan_id');
    }

    public function loanSettlement()
    {
        return $this->hasMany(LoanSettlement::class, 'home_loan_id');
    }

    public function recoveryScheduleLoan()
    {
        return $this->hasMany(RecoveryScheduleLoan::class, 'home_loan_id');
    }

    public function loanApplicationLog()
    {
        return $this->hasMany(LoanApplicationLog::class, 'loan_application_id');
    }

    public static function getCityName($id)
    {
        return DB::table('cities')->select('name')->find($id);
    }

    public static function getStateName($id)
    {
        return DB::table('states')->select('name')->find($id);
    }

    public static function fetchRecord($id)
    {
        return self::with([
            'addresses',
            'employerDetails',
            'bankAccounts',
            'loanIncomes.loanIncomeIndividualDetails',
            'otherDetails',
            'loanOtherGuarantors',
            'proposedLoans.loanProposedInsurancePolicy',
            'proposedLoans.loanProposedTermDeposit',
            'proposedLoans.loanProposedMoveableAsset',
            'guarantorCoApplicants.loanGuarantorCoApplicantInsurancePolicy',
            'guarantorCoApplicants.loanGuarantorCoApplicantTermDeposit',
            'guarantorCoApplicants.loanGuarantorCoApplicantMoveableAsset',
            'guarantorCoApplicants.loanGuarantorCoApplicantLegalHeir',
            'loanGuarApplicant.loanGuarApplicantInsurancePolicies',
            'loanGuarApplicant.loanGuarApplicantLegalHeirs',
            'loanGuarApplicant.loanGuarApplicantMoveableAssets',
            'loanGuarApplicant.loanGuarApplicantTermDeposits',
            'documents',
            'disbursalLoan',
            'recoveryScheduleLoan',
            'loanDisbursement.loanDisbursementDoc',
            'recoveryLoan.recoveryLoanDoc',
            'loanSettlement',
            'loanApplicationLog'
        ])->find($id);
    }

    public static function createUpdateHomeLoan($request, $edit_loanId)
    {
        $name = ($request->f_name ?? '') . ' ' . ($request->m_name ?? '') . ' ' . ($request->l_name ?? '');
        $proceed_date = $request->status_val == 1 ? date('Y-m-d') : null;
        $image_customer = null;
        if ($request->has('image')) {
            $path = $request->file('image')->store('loan_images', 'public');
            $image_customer = $path;
        } elseif ($request->has('stored_image')) {
            $image_customer = $request->stored_image;
        } else {
            $image_customer = null;
        }
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        if ($edit_loanId == 0) {
            do {
                $appli_no = Helper::reGenerateDocumentNumber($request->series);
                $existingLoan = HomeLoan::where('appli_no', $appli_no)->first();
            } while ($existingLoan !== null);
            //dd('here', $appli_no);
        }
        $appli_no = $request->appli_no;
        // $status = $request->status_val == ConstantHelper::SUBMITTED ? Helper::checkApprovalRequired($request->series) : $request->status_val;
        $status = $request->status_val;

        $userData = Helper::userCheck();

        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $loanable_id = Helper::getAuthenticatedUser()->auth_user_id;

        $homeLoan = HomeLoan::where('id', $edit_loanId)->first();
        $homeLoan = HomeLoan::updateOrCreate([
            'id' => $edit_loanId
        ], [
            'organization_id' => $organization_id,
            'group_id' => $group_id,
            'company_id' => $company_id,
            'document_date' => Carbon::now()->format('Y-m-d'),
            'doc_number_type' => $edit_loanId !== 0 ? $homeLoan->doc_number_type : $request->doc_number_type,
            'doc_reset_pattern' => $edit_loanId !== 0 ? $homeLoan->doc_reset_pattern : $request->doc_reset_pattern,
            'doc_prefix' => $edit_loanId !== 0 ? $homeLoan->doc_prefix : $request->doc_prefix,
            'doc_suffix' => $edit_loanId !== 0 ? $homeLoan->doc_suffix : $request->doc_suffix,
            'doc_no' => $edit_loanId !== 0 ? $homeLoan->doc_no : $request->doc_no,
            'type' => 1,
            'series' => $edit_loanId !== 0 ? $homeLoan->series : $request->series,
            'appli_no' => $edit_loanId !== 0 ? $homeLoan->appli_no : $appli_no,
            'book_id' => $edit_loanId !== 0 ? $homeLoan->series : $request->series,
            'ref_no' => $request->ref_no,
            'loan_amount' => $request->loan_amount,
            'scheme_for' => $request->scheme_for,
            'name' => $name,
            'gender' => $request->gender,
            'cast' => $request->cast,
            'marital_status' => $request->marital_status,
            'father_mother_name' => $request->father_mother_name,
            'gir_no' => $request->gir_no,
            'dob' => $request->dob,
            'age' => $request->age,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'spouse_name' => $request->spouse_name,
            'no_of_depends' => $request->no_of_depends,
            'no_of_children' => $request->no_of_children,
            'earning_member' => $request->earning_member,
            'image' => $image_customer,
            'approvalStatus' => $status,
            'approvalLevel'=>$edit_loanId !== 0 ? $homeLoan->approvalLevel : 1,
            'proceed_date' => $proceed_date,
            'book_type_id' => $request->book_type,
            'loanable_id' => $loanable_id,
            'loanable_type' => $userData['user_type']
        ]);

        return $homeLoan;
    }

    public static function deleteHomeLoanAndRelatedRecords($homeLoanId)
    {
        DB::beginTransaction();

        try {
            DB::table('erp_loan_proposed_moveable_assets')
                ->where('loan_proposed_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_loan_proposed_term_deposits')
                ->where('loan_proposed_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_loan_proposed_insurance_policies')
                ->where('loan_proposed_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_loan_other_details')
                ->where('home_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_loan_documents')
                ->where('home_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_loan_income_individual_details')
                ->whereIn('loan_income_id', function ($query) use ($homeLoanId) {
                    $query->select('id')
                        ->from('erp_loan_incomes')
                        ->where('home_loan_id', $homeLoanId);
                })
                ->delete();

            DB::table('erp_loan_incomes')
                ->where('home_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_loan_bank_accounts')
                ->where('home_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_loan_proposed_loans')
                ->where('home_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_loan_employer_details')
                ->where('home_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_loan_addresses')
                ->where('home_loan_id', $homeLoanId)
                ->delete();

            DB::table('erp_home_loans')
                ->where('id', $homeLoanId)
                ->delete();

            // Delete from guarantor tables
            DB::table('erp_loan_guarantor_co_applicant_insurance_policies')
                ->whereIn('loan_guarantor_co_applicant_id', function ($query) use ($homeLoanId) {
                    $query->select('id')
                        ->from('erp_loan_guarantor_co_applicants')
                        ->where('home_loan_id', $homeLoanId);
                })
                ->delete();

            DB::table('erp_loan_guarantor_co_applicant_legal_heirs')
                ->whereIn('loan_guarantor_co_applicant_id', function ($query) use ($homeLoanId) {
                    $query->select('id')
                        ->from('erp_loan_guarantor_co_applicants')
                        ->where('home_loan_id', $homeLoanId);
                })
                ->delete();

            DB::table('erp_loan_guarantor_co_applicant_moveable_assets')
                ->whereIn('loan_guarantor_co_applicant_id', function ($query) use ($homeLoanId) {
                    $query->select('id')
                        ->from('erp_loan_guarantor_co_applicants')
                        ->where('home_loan_id', $homeLoanId);
                })
                ->delete();

            DB::table('erp_loan_guarantor_co_applicant_term_deposits')
                ->whereIn('loan_guarantor_co_applicant_id', function ($query) use ($homeLoanId) {
                    $query->select('id')
                        ->from('erp_loan_guarantor_co_applicants')
                        ->where('home_loan_id', $homeLoanId);
                })
                ->delete();

            DB::table('erp_loan_guarantor_co_applicants')
                ->where('home_loan_id', $homeLoanId)
                ->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete home loan and related records: ' . $e->getMessage());
        }
    }
}
