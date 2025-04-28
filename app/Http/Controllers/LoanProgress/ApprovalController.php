<?php

namespace App\Http\Controllers\LoanProgress;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\Employee;
use App\Models\ErpDocument;
use App\Models\ErpDprTemplateMaster;
use App\Models\ErpLoanAppraisal;
use App\Models\ErpLoanAppraisalDisbursal;
use App\Models\ErpLoanAppraisalDocument;
use App\Models\ErpLoanAppraisalDpr;
use App\Models\ErpLoanAppraisalRecovery;
use App\Models\LoanAppraisalScoring;
use App\Models\LoanApproval;
use App\Models\LoanReject;
use App\Models\LoanReturn;
use App\Models\LoanDisbursement;
use App\Models\RecoveryLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Book;
use App\Models\Ledger;
use App\Models\Group;
use App\Models\Bank;
use App\Models\HomeLoan;
use Illuminate\Support\Facades\DB;
use App\Models\User;

use App\Models\InterestRateScore;
use App\Models\LoanAssessment;
use App\Models\LoanManagement;
use App\Models\TermLoanPromoter;
use App\Helpers\ConstantHelper;
use App\Models\VehicleLoan;
use App\Http\Controllers\LoanManagement\LoanNotificationController;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {

     $organization_id = Helper::getAuthenticatedUser()->organization_id;


        if ($request->ajax()) {
            $loans = ErpLoanAppraisal::with('loan')
                ->join('erp_home_loans as loan', 'erp_loan_appraisals.loan_id', '=', 'loan.id')->orderBy('loan.id', 'desc');            
                $loans->whereIn('loan.approvalStatus', ['assessment','Assessed',ConstantHelper::PARTIALLY_APPROVED]);

            if ($request->ledger) {
                $loans->where('loan.name', 'like', '%' . $request->ledger . '%');
            }
            if ($request->status) {
                $loans->where('loan.approvalStatus', $request->status);
            }
            if ($request->type) {
                $loans->where('loan.type', $request->type);
            }
            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($dates[0]));
                $end = date('Y-m-d', strtotime($dates[1]));
                $loans->whereDate('loan.created_at', '>=', $start)->whereDate('created_at', '<=', $end);
            }
            if (!empty($organization_id)) {
                $loans->where('loan.organization_id', $organization_id);
            }
            $loans = $loans->get();
            $sr_no = 1;

            return DataTables::of($loans)
                ->addColumn('sr_no', function () use (&$sr_no) {
                    return $sr_no++; // Increment and return the serial number
                })
                ->addColumn('appli_no', function ($loan) {
                    return $loan->loan->appli_no ? $loan->loan->appli_no : '-';
                })
                ->addColumn('ref_no', function ($loan) {
                    return $loan->loan->ref_no ? $loan->loan->ref_no : '-';
                })
                ->addColumn('proceed_date', function ($loan) {
                    return $loan->loan->created_at ? \Carbon\Carbon::parse($loan->loan->created_at)->format('d-m-Y') : '-';
                })
                ->addColumn('name', function ($loan) {
                    return $loan->loan->name ? $loan->loan->name : '-';
                })
                ->addColumn('email', function ($loan) {
                    return $loan->loan->email ? $loan->loan->email : '-';
                })
                ->addColumn('mobile', function ($loan) {
                    return $loan->loan->mobile ? $loan->loan->mobile : '-';
                })
                ->addColumn('type', function ($loan) {
                    if ($loan->loan->type == 1) {
                        $type = 'Home';
                    } elseif ($loan->loan->type == 2) {
                        $type = 'Vehicle';
                    } else {
                        $type = 'Term';
                    }
                    return $type;
                })
                ->addColumn('loan_amount', function ($loan) {
                    return $loan->term_loan ? Helper::formatIndianNumber($loan->term_loan) : '-';
                })
                ->addColumn('action', function ($loan) {
                    $view_route = '';
                    if ($loan->loan->type == 1) {
                        $view_route = 'loanApproval.viewHomeLoan';
                    } elseif ($loan->loan->type == 2) {
                        $view_route = 'loanApproval.viewVehicleLoan';
                    } else {
                        $view_route = 'loanApproval.viewTermLoan';
                    }
                    $view_url = route($view_route, $loan->loan->id);

                    return '
                    <a class="dropdown-item" href="' . $view_url . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye me-50"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </a>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } else {
            $loans = ErpLoanAppraisal::with('loan')
                ->select('erp_loan_appraisals.id', 'loan.name')
                ->join('erp_home_loans as loan', 'erp_loan_appraisals.loan_id', '=', 'loan.id')
                ->where('loan.organization_id', $organization_id)
                ->get();
        }

        $loans = ErpLoanAppraisal::with('loan')->orderByDesc('id')->get();

        return view('loanProgress.approval.list', compact('loans'));
    }

    public function viewHomeLoan($id)
    {

        $user = Helper::getAuthenticatedUser();
        $homeLoan = HomeLoan::fetchRecord($id);
        if ($homeLoan && $homeLoan->loanApplicationLog) {
            $logs = $homeLoan->loanApplicationLog->sortByDesc('id');
            $logsGroupedByStatus = $logs->groupBy('action_type');
        } else {
            $logsGroupedByStatus = [];
        }
        $occupation = DB::table('erp_loan_occupations')->get();
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        $parentURL = "loan_home-loan";

         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

        $view_detail = 1;
        $interest_rate = '';
        if (!empty($homeLoan->ass_cibil)) {
            $interest_rate = InterestRateScore::where('cibil_score_min', '<=', $homeLoan->ass_cibil)
                ->where('cibil_score_max', '>=', $homeLoan->ass_cibil)
                ->select('interest_rate')
                ->first();
        }
        $page = "view_detail";

        $behalf_users = Employee::all();

        $loan_disbursement = LoanDisbursement::with('homeLoan')->where('home_loan_id', $id)->get();
        $recovery_loan = RecoveryLoan::with('homeLoan')->where('home_loan_id', $id)->get();

        $overview = ErpLoanAppraisal::with('loan', 'disbursal', 'recovery', 'dpr')->where('loan_id', $id)->first();
        // if (!$overview) {
        //     $overview = (object) [
        //         'created_at' => 'N/A',
        //         'unit_name' => 'N/A',
        //         'proprietor_name' => 'N/A',
        //         'address' => 'N/A',
        //         'cibil_score' => 'N/A',
        //         'project_cost' => 'N/A',
        //         'term_loan' => 'N/A',
        //         'promotor_contribution' => 'N/A',
        //         'interest_rate' => 'N/A',
        //         'loan_period' => 'N/A',
        //         'repayment_type' => 'N/A',
        //         'no_of_installments' => 'N/A',
        //         'repayment_start_after' => 'N/A',
        //         'repayment_start_period' => 'N/A',
        //         'dpr' => [],
        //         'disbursal' => [],
        //         'recovery' => [],
        //     ];
        // }

        $document_listing = Helper::documentListing($id);

        $homeLoan->loanable_type = strtolower(class_basename($homeLoan->loanable_type));
        $buttons = Helper::actionButtonDisplayForLoan($homeLoan->series, $homeLoan->approvalStatus,$homeLoan->id,$homeLoan->loan_amount,$homeLoan->approval_level,$homeLoan->loanable_id,$homeLoan->loanable_type);        $logs = Helper::getLogs($id);

        $logs = Helper::getLogs($id);
        $banks = Bank::withDefaultGroupCompanyOrg()->with('bankDetails')
            ->get();
        $groupId = Group::where('name', 'Stock-in-Hand')->value('id');

        $ledgers = Ledger::withDefaultGroupCompanyOrg()
            ->where(function ($query) use ($groupId) {
                $query->whereJsonContains('ledger_group_id', $groupId)
                    ->orWhere('ledger_group_id', $groupId);
            })->get();
        return view('loanProgress.viewHomeLoan', compact('banks', 'ledgers','homeLoan', 'occupation', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'behalf_users', 'overview', 'document_listing', 'loan_disbursement', 'recovery_loan', 'buttons', 'logs'));
    }

    public function viewVehicleLoan($id)
    {
        $user = Helper::getAuthenticatedUser();
        $vehicleLoan = VehicleLoan::fetchRecord($id);
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        $parentURL = "loan_vehicle-loan";

         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

        if ($vehicleLoan && $vehicleLoan->loanApplicationLog) {
            $logs = $vehicleLoan->loanApplicationLog->sortByDesc('id');
            $logsGroupedByStatus = $logs->groupBy('action_type');
        } else {
            $logsGroupedByStatus = [];
        }
        $view_detail = 1;
        $interest_rate = '';
        if (!empty($vehicleLoan->ass_cibil)) {
            $interest_rate = InterestRateScore::where('cibil_score_min', '<=', $vehicleLoan->ass_cibil)
                ->where('cibil_score_max', '>=', $vehicleLoan->ass_cibil)
                ->select('interest_rate')
                ->first();
        }
        $page = "view_detail";
        $behalf_users = Employee::all();

        $loan_disbursement = LoanDisbursement::with('homeLoan')->where('home_loan_id', $id)->get();
        $recovery_loan = RecoveryLoan::with('homeLoan')->where('home_loan_id', $id)->get();

        $overview = ErpLoanAppraisal::with('loan', 'disbursal', 'recovery', 'dpr')->where('loan_id', $id)->first();
        // if (!$overview) {
        //     $overview = (object) [
        //         'created_at' => 'N/A',
        //         'unit_name' => 'N/A',
        //         'proprietor_name' => 'N/A',
        //         'address' => 'N/A',
        //         'cibil_score' => 'N/A',
        //         'project_cost' => 'N/A',
        //         'term_loan' => 'N/A',
        //         'promotor_contribution' => 'N/A',
        //         'interest_rate' => 'N/A',
        //         'loan_period' => 'N/A',
        //         'repayment_type' => 'N/A',
        //         'no_of_installments' => 'N/A',
        //         'repayment_start_after' => 'N/A',
        //         'repayment_start_period' => 'N/A',
        //         'dpr' => [],
        //         'disbursal' => [],
        //         'recovery' => [],
        //     ];
        // }

        $document_listing = Helper::documentListing($id);

        $vehicleLoan->loanable_type = strtolower(class_basename($vehicleLoan->loanable_type));
$buttons = Helper::actionButtonDisplayForLoan($vehicleLoan->series, $vehicleLoan->approvalStatus,$vehicleLoan->id,$vehicleLoan->loan_amount,$vehicleLoan->approval_level,$vehicleLoan->loanable_id,$vehicleLoan->loanable_type);
$logs = Helper::getLogs($id);
        $banks = Bank::withDefaultGroupCompanyOrg()->with('bankDetails')
            ->get();
        $groupId = Group::where('name', 'Stock-in-Hand')->value('id');

        $ledgers = Ledger::withDefaultGroupCompanyOrg()
            ->where(function ($query) use ($groupId) {
                $query->whereJsonContains('ledger_group_id', $groupId)
                    ->orWhere('ledger_group_id', $groupId);
            })->get();

        return view('loanProgress.viewVehicleLoan', compact('banks', 'ledgers','vehicleLoan', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'behalf_users', 'overview', 'document_listing', 'loan_disbursement', 'recovery_loan', 'buttons', 'logs'));
    }



    public function viewTermLoan($id)
    {

        $user = Helper::getAuthenticatedUser();
        $termLoan = TermLoanPromoter::fetchRecord($id);
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        $parentURL = "loan_term-loan";

         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

        if ($termLoan && $termLoan->loanApplicationLog) {
            $logs = $termLoan->loanApplicationLog->sortByDesc('id');
            $logsGroupedByStatus = $logs->groupBy('action_type');
        } else {
            $logsGroupedByStatus = [];
        }
        $view_detail = 1;
        $interest_rate = '';
        if (!empty($termLoan->ass_cibil)) {
            $interest_rate = InterestRateScore::where('cibil_score_min', '<=', $termLoan->ass_cibil)
                ->where('cibil_score_max', '>=', $termLoan->ass_cibil)
                ->select('interest_rate')
                ->first();
        }
        $page = "view_detail";

        $behalf_users = Employee::all();

        $loan_disbursement = LoanDisbursement::with('homeLoan')->where('home_loan_id', $id)->get();
        $recovery_loan = RecoveryLoan::with('homeLoan')->where('home_loan_id', $id)->get();

        $overview = ErpLoanAppraisal::with('loan', 'disbursal', 'recovery', 'dpr')->where('loan_id', $id)->first();
        // if (!$overview) {
        //     $overview = (object) [
        //         'created_at' => 'N/A',
        //         'unit_name' => 'N/A',
        //         'proprietor_name' => 'N/A',
        //         'address' => 'N/A',
        //         'cibil_score' => 'N/A',
        //         'project_cost' => 'N/A',
        //         'term_loan' => 'N/A',
        //         'promotor_contribution' => 'N/A',
        //         'interest_rate' => 'N/A',
        //         'loan_period' => 'N/A',
        //         'repayment_type' => 'N/A',
        //         'no_of_installments' => 'N/A',
        //         'repayment_start_after' => 'N/A',
        //         'repayment_start_period' => 'N/A',
        //         'dpr' => [],
        //         'disbursal' => [],
        //         'recovery' => [],
        //     ];
        // }

        $document_listing = Helper::documentListing($id);
        $termLoan->loanable_type = strtolower(class_basename($termLoan->loanable_type));

        $buttons = Helper::actionButtonDisplayForLoan($termLoan->series, $termLoan->approvalStatus, $termLoan->id, $termLoan->loan_amount,$termLoan->approval_level,$termLoan->loanable_id,$termLoan->loanable_type);

        $logs = Helper::getLogs($id);
        $banks = Bank::withDefaultGroupCompanyOrg()->with('bankDetails')
            ->get();
        $groupId = Group::where('name', 'Stock-in-Hand')->value('id');

        $ledgers = Ledger::withDefaultGroupCompanyOrg()
            ->where(function ($query) use ($groupId) {
                $query->whereJsonContains('ledger_group_id', $groupId)
                    ->orWhere('ledger_group_id', $groupId);
            })->get();
        return view('loanProgress.viewTermLoan', compact('banks', 'ledgers','termLoan', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'behalf_users', 'overview', 'document_listing', 'loan_disbursement', 'recovery_loan', 'buttons', 'logs', 'buttons'));
    }
    function loanApprove(Request $request)
    {
        $user = Helper::getAuthenticatedUser()->id;
        $approver = Helper::userCheck()['user_type'];
        $approver = $approver::find($user);
        $request->validate([
            'loan_application_id' => 'required',
        ]);

        return redirect()->route('loanApproval.approval',$request->loan_application_id);
    }

    function loanReturn(Request $request)
    {

        $request->validate([
            'loan_application_id' => 'required',
            'document' => 'required',
            'behalf_users' => 'required',
            'remarks' => 'required'
        ]);

        $docs = [];
        if ($request->hasFile('document')) {
            // Define the storage path
            $directoryPath = 'loan_documents/return';

            // Check if the directory exists; if not, create it
            if (!Storage::disk('public')->exists($directoryPath)) {
                Storage::disk('public')->makeDirectory($directoryPath);
            }

            // Store the file in the 'loan_documents/assessment' folder
            $documentPath = $request->file('document')->store($directoryPath, 'public');

            HomeLoan::find($request->loan_application_id)->update(['approvalStatus' => 'draft']);

            LoanReturn::create([
                'loan_application_id' => $request->loan_application_id,
                'status' => 'draft',
                'on_behalf' => json_encode($request->behalf_users),
                'return_page_status' => 'approval',
                'doc' => $documentPath,
                'remarks' => $request->remarks
            ]);
            $docs[] = $documentPath;
        }

        $loan_data = HomeLoan::find($request->loan_application_id);

        Helper::logs(
            $loan_data->series,
            $loan_data->appli_no,
            $request->loan_application_id,
            $loan_data->organization_id,
            'Loan Approval',
            $request->remarks,
            $loan_data->loanable_id,
            $docs,
            $loan_data->loanable_type,
            0,
            $loan_data->created_at,
            $loan_data->approvalStatus
        );

        return redirect()->route('loanApproval.view' . ucwords($request->loan_type) . 'Loan', [$request->loan_application_id]);
    }

    function loanReject(Request $request)
    {

        $request->validate([
            'loan_application_id' => 'required',
            'document' => 'required',
            'behalf_users' => 'required',
            'remarks' => 'required'
        ]);

        $docs = [];
        if ($request->hasFile('document')) {
            // Define the storage path
            $directoryPath = 'loan_documents/reject';

            // Check if the directory exists; if not, create it
            if (!Storage::disk('public')->exists($directoryPath)) {
                Storage::disk('public')->makeDirectory($directoryPath);
            }

            // Store the file in the 'loan_documents/assessment' folder
            $documentPath = $request->file('document')->store($directoryPath, 'public');

            HomeLoan::find($request->loan_application_id)->update(['approvalStatus' => 'rejected']);
            $update = HomeLoan::find($request->loan_application_id);

            $approver = Helper::getAuthenticatedUser();
            $created_by = $update->loanable_id;
            $creator = AuthUser::find($created_by);

            LoanNotificationController::notifyLoanReject($creator->authUser(), $update, $approver);




            LoanReject::create([
                'loan_application_id' => $request->loan_application_id,
                'status' => 'rejected',
                'on_behalf' => json_encode($request->behalf_users),
                'doc' => $documentPath,
                'remarks' => $request->remarks
            ]);
            $docs[] = $documentPath;
        }

        $loan_data = HomeLoan::find($request->loan_application_id);

        Helper::logs(
            $loan_data->series,
            $loan_data->appli_no,
            $request->loan_application_id,
            $loan_data->organization_id,
            'Loan Approval',
            $request->remarks,
            $loan_data->loanable_id,
            $docs,
            $loan_data->loanable_type,
            0,
            $loan_data->created_at,
            $loan_data->approvalStatus
        );

        return redirect()->route('loanApproval.view' . ucwords($request->loan_type) . 'Loan', [$request->loan_application_id]);
    }

    public function view()
    {
        return view('loanProgress.approval.view');
    }
    public function approval($id){
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 2;
        }

        $loan = HomeLoan::where('id', $id)->first();

        $loanAppraisal = null;
        $loanAppraisal = ErpLoanAppraisal::where('loan_id', $id)
            ->with('dpr')->with('disbursal')->with('recovery')->with('document')->first();

        $dpr_templates = ErpDprTemplateMaster::where('organization_id', $organization_id)
            ->where('status', 'active')
            ->get();

        $doc_type = ErpDocument::where('organization_id', $organization_id)->where('service', 'loan')->where('status', 'active')->get();

        return view('loanProgress.approval.approval', compact('loan', 'loanAppraisal', 'dpr_templates', 'doc_type'));

//        return view('loanProgress.approval.approval', compact('banks', 'ledgers','vehicleLoan', 'overview', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'document_listing', 'loan_disbursement', 'recovery_loan', 'buttons', 'logs', 'module'));
    }
    public function updateApproval(Request $request) {
        $rules = [
            'loan_id' => 'sometimes|exists:erp_home_loans,id',
            'application_no' => 'sometimes',
            'unit_name' => 'sometimes',
            'proprietor_name' => 'sometimes',
            'address' => 'sometimes',
            'project_cost' => 'sometimes',
            'term_loan' => 'nullable',
            'promotor_contribution' => 'sometimes',
            'cibil_score' => 'sometimes|numeric|min:300|max:900',
            'interest_rate' => 'sometimes',
            'loan_period' => 'sometimes',
            'repayment_type' => 'sometimes',
            'no_of_installments' => 'sometimes',
            'repayment_start_period' => 'sometimes',
            'status' => 'required',
            'dpr_template' => 'nullable|exists:erp_dpr_template_masters,id',
            'dpr.*' => 'nullable',
            'disbursal_milestone.*' => 'sometimes|string|max:255',
            'disbursal_amount.*' => 'sometimes|string|min:1',
            'disbursal_remarks.*' => 'nullable|string|max:255',
            'checkbox_data' => 'nullable|json',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Please check all inputs'
            ]);
        }

        DB::beginTransaction();
        try {
            $loanAppraisal = ErpLoanAppraisal::updateOrCreate(
                ['loan_id' => $request->loan_id],
                $request->only([
                    'loan_id',
                    'application_no',
                    'unit_name',
                    'proprietor_name',
                    'address',
                    'project_cost',
                    'term_loan',
                    'promotor_contribution',
                    'cibil_score',
                    'interest_rate',
                    'loan_period',
                    'repayment_type',
                    'no_of_installments',
                    'repayment_start_period',
                    'status',
                    'group_id',
                    'company_id',
                    'organization_id',
                ])
            );

            $loan_appraisal_id = $loanAppraisal->id;

            if ($request->has('disbursal_amount')) {
                ErpLoanAppraisalDisbursal::where('loan_appraisal_id', $loan_appraisal_id)->delete();
                foreach ($request->disbursal_amount as $key => $disbursal_amount) {
                    ErpLoanAppraisalDisbursal::create([
                        'loan_appraisal_id' => $loan_appraisal_id,
                        'milestone' => $request->disbursal_milestone[$key],
                        'amount' => Helper::removeCommas($disbursal_amount),
                        'remarks' => $request->disbursal_remarks[$key]
                    ]);
                }
            }
            $docs = [];

            $loan_detail = HomeLoan::find($request->loan_id)->first();
            if ($loan_detail->type == 1) {
                $loan_type = 'Home Loan';
            } elseif ($loan_detail->type == 2) {
                $loan_type = 'Vehicle Loan';
            } else {
                $loan_type = 'Term Loan';
            }

            Helper::logs(
                $loan_detail->series,
                $loan_detail->appli_no,
                $loan_detail->id,
                $loan_detail->organization_id,
                $loan_type,
                '-',
                $loan_detail->type,
                $docs,
                $loan_detail->loanable_type,
                0,
                $loan_detail->created_at,
                $request->status
            );

            $checkboxData = json_decode($request->checkbox_data, true);
            LoanAppraisalScoring::createOrUpdate([
                'loan_id' => $request->loan_id,
                'loan_appraisal_id' => $loan_appraisal_id,
                'loan_type' => $loan_type,
                'financial_analysis' => $checkboxData['financial_analysis'] ?? [],
                'collateral_1' => $checkboxData['collateral_1'] ?? [],
                'collateral_2' => $checkboxData['collateral_2'] ?? [],
                'compliance_and_risk' => $checkboxData['compliance_and_risk'] ?? [],
                'community' => $checkboxData['community'] ?? [],
            ]);

                $update = HomeLoan::find($request->loan_id);
                $approver = Helper::getAuthenticatedUser();
                $creator =  AuthUser::find($update->loanable_id);
                $approveDocument = Helper::approveDocument($update->series, $update->id, 0, $request->remarks, '', $update->approvalLevel, 'approve', $update->loan_amount);

                $update->approvalLevel = $approveDocument['nextLevel'];
                $update->approvalStatus = $approveDocument['approvalStatus'];
                $update->save();

                if ($update->approvalStatus == ConstantHelper::APPROVED || $update->approvalStatus == 'approved' || $update->approvalStatus == ConstantHelper::PARTIALLY_APPROVED) {
                    LoanNotificationController::notifyLoanApproved($creator->authUser(), $update, $approver);

                    LoanApproval::create([
                        'loan_application_id' => $request->loan_id,
                        'status' => 'approved',
                        'doc' => '',
                        'remarks' => ''
                    ]);
                }


            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in updateApproval method:', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
            ]);

            // Return a detailed error response
            return response()->json([
                'status' => false,
                'message' => 'Transaction failed',
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage(),
                ],
            ]);
        }
    }
}
