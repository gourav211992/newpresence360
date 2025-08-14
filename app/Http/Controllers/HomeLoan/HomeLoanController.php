<?php

namespace App\Http\Controllers\HomeLoan;

use Carbon\Carbon;
use App\Models\Book;

use App\Helpers\Helper;
use App\Models\HomeLoan;
use App\Models\LoanIncome;
use App\Models\LoanAddress;
use App\Models\LoanDocument;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\NumberPattern;
use App\Models\LoanManagement;
use App\Models\LoanBankAccount;
use App\Models\LoanOtherDetail;
use App\Models\LoanProposedLoan;
use App\Mail\LoanUserMessageMail;
use App\Models\LoanGuarApplicant;
use App\Models\LoanApplicationLog;
use App\Models\LoanEmployerDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\LoanGuarantorCoApplicant;
use App\Http\Requests\StoreHomeLoanRequest;

class HomeLoanController extends Controller
{
    public function add()
    {
        $occupation = DB::table('erp_loan_occupations')->get();
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        return view('loan.home_loan_add', compact('occupation', 'series', 'book_type'));
    }

    public function create(StoreHomeLoanRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = Helper::getAuthenticatedUser();
            $edit_loanId = isset($request->edit_loanId) ? $request->edit_loanId : 0;
            $homeLoan = HomeLoan::createUpdateHomeLoan($request, $edit_loanId);

            if (!$homeLoan) {
                return redirect("loan/my-application")->with('error', 'Something went wrong.');
            }
            if ($homeLoan) {
                if ($request->status_val == 1 && isset($request->email)) {
                    // $date = Carbon::now()->format("Y-m-d H:i:s");
                    // $email_data = [
                    //     'username' => (string) $homeLoan->name,
                    //     'title' => "Application Submission Confirmation",
                    //     'message' => "Dear {$homeLoan->name}, your loan application (Application ID: {$homeLoan->appli_no}) has been successfully submitted on {$date}. We will review your application and update you on the status accordingly. Thank you for choosing MIDC",
                    // ];

                    // try {
                    //     Mail::to($homeLoan->email)->send(new LoanUserMessageMail($email_data));
                    //     // Log success
                    //     Log::info("Email sent successfully to {$homeLoan->email} for application {$homeLoan->appli_no}");
                    // } catch (\Exception $e) {
                    //     // Log the full error
                    //     return redirect()->back()->with("error", "Failed to send email. Please try again later.");
                    // }
                }
            }
            LoanApplicationLog::logCreation($request, $homeLoan, 'home', parent::authUserId());
            LoanAddress::createUpdateAddress($request, $edit_loanId, $homeLoan);
            LoanEmployerDetail::createUpdateEmployer($request, $edit_loanId, $homeLoan);
            LoanBankAccount::createUpdateBankAccount($request, $edit_loanId, $homeLoan);
            LoanIncome::createUpdateLoanIncome($request, $edit_loanId, $homeLoan);
            LoanProposedLoan::createUpdateProposed($request, $edit_loanId, $homeLoan);
            LoanOtherDetail::createUpdateOtherDetail($request, $edit_loanId, $homeLoan);
            LoanGuarantorCoApplicant::createUpdateGuarantor($request, $edit_loanId, $homeLoan);
            LoanGuarApplicant::createUpdateGuar($request, $edit_loanId, $homeLoan);
            LoanDocument::createUpdateDocument($request, $edit_loanId, $homeLoan);
            $organization = Organization::getOrganization();
            $book_type = (int) $request->series;
            if (!isset($request->edit_loanId)) {
                if ($organization) {
                    NumberPattern::incrementIndex($organization->id, $book_type);
                }
            }

            Helper::logs(
                $request->series,
                $request->appli_no,
                $homeLoan->id,
                $organization->id,
                'Home Loan',
                '-',
                $homeLoan->type,
                '-',
                $homeLoan->loanable_type,
                0,
                $homeLoan->created_at,
                $homeLoan->approvalStatus
            );

            DB::commit();
            return redirect("loan/my-application")->with('success', 'Home Loan created/updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect("loan/my-application")->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = Helper::getAuthenticatedUser();
        $homeLoan = HomeLoan::fetchRecord($id);
        $editData = 1;
        $occupation = DB::table('erp_loan_occupations')->get();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        $parentURL = "loan_home-loan";
        
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
        $creatorType = explode("\\", $homeLoan->loanable_type);
        // $buttons = Helper::actionButtonDisplay($homeLoan->series, $homeLoan->approvalStatus, $homeLoan->id, $homeLoan->loan_amount, $homeLoan->approvalLevel, $homeLoan->loanable_id, strtolower(end($creatorType)));
        $history = Helper::getApprovalHistory($homeLoan->series, $id, 0);

        $homeLoan->loanable_type = strtolower(class_basename($homeLoan->loanable_type));
$buttons = Helper::actionButtonDisplayForLoan($homeLoan->series, $homeLoan->approvalStatus,$homeLoan->id,$homeLoan->loan_amount,$homeLoan->approval_level,$homeLoan->loanable_id,$homeLoan->loanable_type);        
        $page = "edit";
        return view('loan.home_loan_add', compact('homeLoan', 'editData', 'occupation', 'series', 'book_type', 'buttons', 'history', 'page'));
    }

    public function destroy($id)
    {
        $homeLoan = HomeLoan::deleteHomeLoanAndRelatedRecords($id);
        return redirect("loan/my-application")->with('success', 'Home loan and related records deleted successfully.');
    }
}
