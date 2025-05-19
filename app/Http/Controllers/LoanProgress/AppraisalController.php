<?php

namespace App\Http\Controllers\LoanProgress;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ErpDocument;
use App\Models\ErpDprMaster;
use App\Models\ErpDprTemplateMaster;
use App\Models\ErpLoanAppraisal;
use App\Models\ErpLoanAppraisalDisbursal;
use App\Models\ErpLoanAppraisalDocument;
use App\Models\ErpLoanAppraisalDpr;
use App\Models\ErpLoanAppraisalRecovery;
use App\Models\Ledger;
use App\Models\Group;
use App\Models\Bank;
use App\Models\HomeLoan;
use App\Models\InterestRate;
use App\Models\InterestRateScore;
use App\Models\LoanAppraisalScoring;
use App\Models\LoanDisbursement;
use App\Models\LoanLog;
use App\Models\LoanManagement;
use App\Models\LoanReturn;
use App\Models\RecoveryLoan;
use App\Models\TermLoanPromoter;
use App\Models\VehicleLoan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Route;

class AppraisalController extends Controller
{

    public function index(Request $request)
    {

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        if ($request->ajax()) {

            $loans = HomeLoan::orderBy('id', 'desc');
            $loans->where('approvalStatus', 'submitted');

            if ($request->ledger) {
                $loans->where('name', 'like', '%' . $request->ledger . '%');
            }
            if ($request->status) {
                $loans->where('approvalStatus', $request->status);
            }
            if ($request->type) {
                $loans->where('type', $request->type);
            }
            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($dates[0]));
                $end = date('Y-m-d', strtotime($dates[1]));
                $loans->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
            }
            if (!empty($organization_id)) {
                $loans->where('organization_id', $organization_id);
            }
            $loans = $loans->get();
            $sr_no = 1;

            return DataTables::of($loans)
                ->addColumn('sr_no', function () use (&$sr_no) {
                    return $sr_no++; // Increment and return the serial number
                })
                ->addColumn('appli_no', function ($loan) {
                    return $loan->appli_no ? $loan->appli_no : '-';
                })
                ->addColumn('ref_no', function ($loan) {
                    return $loan->ref_no ? $loan->ref_no : '-';
                })
                ->addColumn('proceed_date', function ($loan) {
                    return $loan->created_at ? \Carbon\Carbon::parse($loan->created_at)->format('d-m-Y') : '-';
                })
                ->addColumn('name', function ($loan) {
                    return $loan->name ? $loan->name : '-';
                })
                ->addColumn('email', function ($loan) {
                    return $loan->email ? $loan->email : '-';
                })
                ->addColumn('mobile', function ($loan) {
                    return $loan->mobile ? $loan->mobile : '-';
                })
                ->addColumn('type', function ($loan) {
                    if ($loan->type == 1) {
                        $type = 'Home';
                    } elseif ($loan->type == 2) {
                        $type = 'Vehicle';
                    } else {
                        $type = 'Term';
                    }
                    return $type;
                })
                ->addColumn('loan_amount', function ($loan) {
                    return $loan->loan_amount ? Helper::formatIndianNumber($loan->loan_amount) : '-';
                })
                ->addColumn('action', function ($loan) {
                    $view_route = '';
                    if ($loan->type == 1) {
                        $view_route = 'loanAppraisal.viewHomeLoan';
                    } elseif ($loan->type == 2) {
                        $view_route = 'loanAppraisal.viewVehicleLoan';
                    } else {
                        $view_route = 'loanAppraisal.viewTermLoan';
                    }
                    $view_url = route($view_route, $loan->id);

                    return '
                    <a class="dropdown-item" href="' . $view_url . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye me-50"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </a>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } else {
            $loans = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->where('organization_id', $organization_id)->get();
        }

        return view('loanProgress.appraisal.list', compact('loans'));
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

        $overview = null;
        $overview = ErpLoanAppraisal::where('loan_id', $id)
            ->with('dpr')->with('disbursal')->with('recovery')->with('document')->first();

        $loan_disbursement = LoanDisbursement::with('homeLoan')->where('home_loan_id', $id)->get();
        $recovery_loan = RecoveryLoan::with('homeLoan')->where('home_loan_id', $id)->get();

        $document_listing = Helper::documentListing($id);

        $homeLoan->loanable_type = strtolower(class_basename($homeLoan->loanable_type));
        $buttons = Helper::actionButtonDisplayForLoan($homeLoan->series, $homeLoan->approvalStatus,$homeLoan->id,$homeLoan->loan_amount,$homeLoan->approval_level,$homeLoan->loanable_id,$homeLoan->loanable_type);        $logs = Helper::getLogs($id);

        $logs = Helper::getLogs($id);

        $module = 'appraisal';
        $banks = Bank::withDefaultGroupCompanyOrg()->with('bankDetails')
            ->get();
        $groupId = Group::where('name', 'Stock-in-Hand')->value('id');

        $ledgers = Ledger::withDefaultGroupCompanyOrg()
            ->where(function ($query) use ($groupId) {
                $query->whereJsonContains('ledger_group_id', $groupId)
                    ->orWhere('ledger_group_id', $groupId);
            })->get();
        $allledgers = Ledger::withDefaultGroupCompanyOrg()->get();
        $isPostingRequired = false;

        $groups = Group::where('status', 'active')->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('parent_group_id')->whereNull('organization_id');
            })->orWhere('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->select('id', 'name')->get();


        return view('loanProgress.viewHomeLoan', compact('banks', 'ledgers','groups','homeLoan','isPostingRequired','allledgers','overview', 'occupation', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'document_listing', 'loan_disbursement', 'recovery_loan', 'buttons', 'logs', 'module'));
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

        $overview = null;
        $overview = ErpLoanAppraisal::where('loan_id', $id)
            ->with('dpr')->with('disbursal')->with('recovery')->with('document')->first();

        $loan_disbursement = LoanDisbursement::with('homeLoan')->where('home_loan_id', $id)->get();
        $recovery_loan = RecoveryLoan::with('homeLoan')->where('home_loan_id', $id)->get();

        $document_listing = Helper::documentListing($id);

        $vehicleLoan->loanable_type = strtolower(class_basename($vehicleLoan->loanable_type));
        $buttons = Helper::actionButtonDisplayForLoan($vehicleLoan->series, $vehicleLoan->approvalStatus,$vehicleLoan->id,$vehicleLoan->loan_amount,$vehicleLoan->approval_level,$vehicleLoan->loanable_id,$vehicleLoan->loanable_type);        $logs = Helper::getLogs($id);

        $module = 'appraisal';
        $banks = Bank::withDefaultGroupCompanyOrg()->with('bankDetails')
            ->get();
        $groupId = Group::where('name', 'Stock-in-Hand')->value('id');

        $ledgers = Ledger::withDefaultGroupCompanyOrg()
            ->where(function ($query) use ($groupId) {
                $query->whereJsonContains('ledger_group_id', $groupId)
                    ->orWhere('ledger_group_id', $groupId);
            })->get();

        return view('loanProgress.viewVehicleLoan', compact('banks', 'ledgers','vehicleLoan', 'overview', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'document_listing', 'loan_disbursement', 'recovery_loan', 'buttons', 'logs', 'module'));
        // return view('loanProgress.appraisal.viewVehicleLoan', compact('vehicleLoan', 'overview', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'document_listing', 'loan_disbursement', 'recovery_loan', 'buttons'));

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

        $overview = null;
        // $overview = ErpLoanAppraisal::where('loan_id', $id)
        $overview = ErpLoanAppraisal::where('loan_id', $id)
            ->with('dpr')->with('disbursal')->with('recovery')->with('document')->first();

        $loan_disbursement = LoanDisbursement::with('homeLoan')->where('home_loan_id', $id)->get();
        $recovery_loan = RecoveryLoan::with('homeLoan')->where('home_loan_id', $id)->get();

        $document_listing = Helper::documentListing($id);

        $termLoan->loanable_type = strtolower(class_basename($termLoan->loanable_type));
        $buttons = Helper::actionButtonDisplayForLoan($termLoan->series, $termLoan->approvalStatus,$termLoan->id,$termLoan->loan_amount,$termLoan->approval_level,$termLoan->loanable_id,$termLoan->loanable_type);
        $logs = Helper::getLogs($id);
        $module = 'appraisal';
        $banks = Bank::withDefaultGroupCompanyOrg()->with('bankDetails')
            ->get();
        $groupId = Group::where('name', 'Stock-in-Hand')->value('id');

        $ledgers = Ledger::withDefaultGroupCompanyOrg()
            ->where(function ($query) use ($groupId) {
                $query->whereJsonContains('ledger_group_id', $groupId)
                    ->orWhere('ledger_group_id', $groupId);
            })->get();

        return view('loanProgress.viewTermLoan', compact('banks', 'ledgers','termLoan', 'overview', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'document_listing', 'loan_disbursement', 'recovery_loan', 'buttons', 'logs', 'module'));
        // return view('loanProgress.appraisal.viewTermLoan', compact('termLoan', 'overview', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'document_listing', 'loan_disbursement', 'recovery_loan', 'buttons'));

    }

    public function create($id)
    {

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

        return view('loanProgress.appraisal.create', compact('loan', 'loanAppraisal', 'dpr_templates', 'doc_type'));

    }

    public function getInterestRate(Request $request)
    {

        $cibil_score = $request->cibil_score;

        // Find the InterestRateScore based on the cibil_score range
        $interestRateScore = InterestRateScore::where('cibil_score_min', '<=', $cibil_score)
            ->where('cibil_score_max', '>=', $cibil_score)
            ->first();

        // Handle case where no InterestRateScore is found
        if (!$interestRateScore) {
            return response()->json(['error' => 'No matching interest rate score found'], 404);
        }

        // Return the base rate
        return response()->json(['base_rate' => $interestRateScore->base_rate], 200);
    }

    public function getDprFields(Request $request)
    {

        $template_id = $request->template_id;

        // Find the InterestRateScore based on the cibil_score range
        $dprFields = ErpDprMaster::where('template_id', $template_id)->get();

        // Handle case where no InterestRateScore is found
        if (!$dprFields) {
            return response()->json(['error' => 'No matching fields found'], 404);
        }

        // Return the base rate
        return response()->json(['dprFields' => $dprFields], 200);
    }


    public function save(Request $request)
    {

        $rules = [
            'loan_id' => 'required|exists:erp_home_loans,id',
            'application_no' => 'required',
            'unit_name' => 'required',
            'proprietor_name' => 'required',
            'address' => 'required',
            'project_cost' => 'required',
            'term_loan' => 'nullable',
            'promotor_contribution' => 'required',
            'cibil_score' => 'required|numeric|min:300|max:900',
            'interest_rate' => 'required',
            'loan_period' => 'required',
            'repayment_type' => 'required',
            'no_of_installments' => 'required',
            // 'repayment_start_after' => 'required',
            'repayment_start_period' => 'required',
            'status' => 'required',
            'dpr_template' => 'nullable|exists:erp_dpr_template_masters,id',
            'dpr.*' => 'nullable',
            'disbursal_milestone.*' => 'required|string|max:255',
            'disbursal_amount.*' => 'required|string|min:1',
            'disbursal_remarks.*' => 'nullable|string|max:255',
            'checkbox_data' => 'nullable|json',
        ];
        $messages = [
            'loan_id.required' => 'The loan ID is required.',
            'loan_id.exists' => 'The loan ID must exist in the ERP home loans table.',
            'application_no.required' => 'The application number is required.',
            'unit_name.required' => 'The unit name is required.',
            'proprietor_name.required' => 'The proprietor name is required.',
            'address.required' => 'The address is required.',
            'project_cost.required' => 'The project cost is required.',
            'promotor_contribution.required' => 'Promotor contribution is required.',
            'cibil_score.required' => 'The CIBIL score is required.',
            'cibil_score.numeric' => 'The CIBIL score must be a number.',
            'cibil_score.min' => 'The CIBIL score must be at least 300.',
            'cibil_score.max' => 'The CIBIL score cannot exceed 900.',
            'interest_rate.required' => 'The interest rate is required.',
            'loan_period.required' => 'The loan period is required.',
            'repayment_type.required' => 'The repayment type is required.',
            'no_of_installments.required' => 'The number of installments is required.',
            // 'repayment_start_after.required' => 'The repayment start period is required.',
            'repayment_start_period.required' => 'The repayment start period is required.',
            'status.required' => 'The status is required.',
            'dpr_template.exists' => 'The selected DPR template is invalid.',
            'disbursal_milestone.*.required' => 'Each disbursal milestone is required.',
            'disbursal_milestone.*.string' => 'Each disbursal milestone must be a string.',
            'disbursal_milestone.*.max' => 'Each disbursal milestone cannot exceed 255 characters.',
            'disbursal_amount.*.required' => 'Each disbursal amount is required.',
            'disbursal_amount.*.numeric' => 'Each disbursal amount must be a number.',
            'disbursal_amount.*.min' => 'Each disbursal amount must be at least 1.',
            'disbursal_remarks.*.string' => 'Each disbursal remark must be a string.',
            'disbursal_remarks.*.max' => 'Each disbursal remark cannot exceed 255 characters.',
        ];
        // dd($request->all());
        // Run the validation
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            // Return validation errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'message' => 'Please check all inputs'
            ]);
        }

        // If validation passes, proceed with the database transaction

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
                    // 'repayment_start_after',
                    'repayment_start_period',
                    'status',
                    'group_id',
                    'company_id',
                    'organization_id',
                ])
            );

            // Get the ID of the loan appraisal
            $loan_appraisal_id = $loanAppraisal->id;

            // Check if DPR data is available, delete old entries and insert new rows
            if ($request->has('dpr')) {
                $dpr_template_id = $request->dpr_template;

                // Delete existing DPR records for this loan appraisal
                ErpLoanAppraisalDpr::where('loan_appraisal_id', $loan_appraisal_id)->delete();

                // Insert new DPR records
                foreach ($request->dpr as $dpr_id => $dpr_value) {
                    ErpLoanAppraisalDpr::create([
                        'loan_appraisal_id' => $loan_appraisal_id,
                        'dpr_template_id' => $dpr_template_id,
                        'dpr_id' => $dpr_id,
                        'dpr_value' => $dpr_value
                    ]);
                }
            }

            if ($request->status == 'submitted') {
                HomeLoan::find($request->loan_id)->update(['approvalStatus' => 'appraisal']);
            }

            // Check if Disbursal data is available, delete old entries and insert new rows
            if ($request->has('disbursal_amount')) {

                // Delete existing Disbursal records for this loan appraisal
                ErpLoanAppraisalDisbursal::where('loan_appraisal_id', $loan_appraisal_id)->delete();

                // Insert new Disbursal records
                foreach ($request->disbursal_amount as $key => $disbursal_amount) {
                    ErpLoanAppraisalDisbursal::create([
                        'loan_appraisal_id' => $loan_appraisal_id,
                        'milestone' => $request->disbursal_milestone[$key],
                        'amount' => Helper::removeCommas($disbursal_amount),
                        'remarks' => $request->disbursal_remarks[$key]
                    ]);
                }
            }

            // Check if Recovery data is available, delete old entries and insert new rows
            if ($request->has('year')) {

                // Delete existing Recovery records for this loan appraisal
                ErpLoanAppraisalRecovery::where('loan_appraisal_id', $loan_appraisal_id)->delete();

                // Insert new Recovery records
                foreach ($request->year as $key => $year) {
                    ErpLoanAppraisalRecovery::create([
                        'loan_appraisal_id' => $loan_appraisal_id,
                        'year' => $year,
                        'start_amount' => Helper::removeCommas($request->start_amount[$key]),
                        'interest_amount' => Helper::removeCommas($request->interest_amount[$key]),
                        'repayment_amount' => Helper::removeCommas($request->repayment_amount[$key]),
                        'end_amount' => Helper::removeCommas($request->end_amount[$key])
                    ]);
                }
            }

            $docs = [];
            // Check if document data is available, delete old entries and insert new rows
            if ($request->has('documentname')) {

                // Insert new document records
                foreach ($request->documentname as $key => $names) {
                    $document_type = $names;

                    if (isset($request->attachments[$key])) {
                        foreach ($request->attachments[$key] as $key1 => $document) {

                            // $documentName = time() . '-' . $document->getClientOriginalName();

                            // // Define the storage path
                            // $directoryPath = 'loan_documents/return';

                            // // Check if the directory exists; if not, create it
                            // if (!Storage::disk('public')->exists($directoryPath)) {
                            //     Storage::disk('public')->makeDirectory($directoryPath);
                            // }

                            // // Store the file in the 'loan_documents/assessment' folder
                            // $document->move(storage_path('app'), $documentName);

                            // Define the storage path
                            $directoryPath = 'loan_documents/appraisal';

                            // Check if the directory exists; if not, create it
                            if (!Storage::disk('public')->exists($directoryPath)) {
                                Storage::disk('public')->makeDirectory($directoryPath);
                            }

                            // Store the file in the 'loan_documents/assessment' folder
                            $documentPath = $document->store($directoryPath, 'public');

                            // Insert the new document record into the database
                            ErpLoanAppraisalDocument::create([
                                'loan_appraisal_id' => $loan_appraisal_id,
                                'document_type' => $document_type,
                                'document' => $documentPath
                            ]);
                            $docs[] = $documentPath;
                        }
                    }
                }
            }

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
                'document_completeness' => $checkboxData['document_completeness'] ?? [],
                'basic_eligibility' => $checkboxData['basic_eligibility'] ?? [],
                'collateral_credit_history' => $checkboxData['collateral_credit_history'] ?? [],
                'credit_data' => $checkboxData['credit_history'] ?? [],
            ]);

            // Commit the transaction
            DB::commit();
            // return redirect()->route('loanAssessment.index');

            // Return success response
            // return redirect()->route('loanAppraisal.index');
            return response()->json([
                'status' => true,
                'message' => 'Record inserted successfully',
            ]);

        } catch (\Exception $e) {
            // Rollback the transaction if there's an error
            DB::rollBack();

            // Return error response
            return response()->json([
                'status' => false,
                'message' => 'Transaction failed',
                'errors' => $e->getMessage(),
            ]);
        }
    }

    public function deleteDocument(Request $request)
    {

        $document_id = $request->document_id;

        // Find the document by ID
        $document = ErpLoanAppraisalDocument::find($document_id);

        if ($document) {
            // Delete the physical file from the server
            $documentPath = public_path('documents/' . $document->document);

            // Check if the file exists and delete it
            if (file_exists($documentPath)) {
                unlink($documentPath); // Delete the file
            }

            // Delete the document record from the database
            $document->delete();

            return response()->json([
                'status' => true,
                'message' => 'Document deleted successfully!',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Document not found!',
            ]);
        }
    }

    function loanReject()
    {

    }

    function loanReturn(Request $request)
    {
        $request->validate([
            'loan_application_id' => 'required',
            'document' => 'required',
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
                'return_page_status' => 'appraisal',
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
            'Loan Appraisal',
            $request->remarks,
            $loan_data->loanable_id,
            $docs,
            $loan_data->loanable_type,
            0,
            $loan_data->created_at,
            $loan_data->approvalStatus
        );

        return redirect()->route('loanAppraisal.view' . ucwords($request->loan_type) . 'Loan', [$request->loan_application_id]);
    }
    public function updateBasicEligibility(Request $request)
    {
        $request->validate([
            'loan_appraisal_id' => 'required|exists:erp_loan_appraisal_credit_scoring,loan_appraisal_id',
            'basic_eligibility' => 'nullable|array',
            'remarks' => 'nullable|string',
        ]);

        try {
            $loanAppraisalId = $request->loan_appraisal_id;
            $remarks = $request->remarks;
            $basicEligibility = $request->basic_eligibility ?? [];

            LoanAppraisalScoring::updateOrCreate(
                ['loan_appraisal_id' => $loanAppraisalId],
                [
                    'basic_eligibility' => $basicEligibility,
                    'remarks' => $remarks
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Basic Eligibility updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Basic Eligibility',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
