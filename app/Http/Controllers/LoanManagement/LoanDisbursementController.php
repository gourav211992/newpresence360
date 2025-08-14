<?php

namespace App\Http\Controllers\LoanManagement;

use App\Helpers\ConstantHelper;
use App\Models\AuthUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Models\HomeLoan;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\NumberPattern;
use App\Models\LoanManagement;
use App\Models\LoanDisbursement;
use App\Models\ErpLoanAppraisalDisbursal;
use App\Models\LoanApplicationLog;
use App\Models\BankDetail;
use App\Models\Bank;
use App\Models\LoanDisbursementDoc;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Exception;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Employee;
use App\Models\User;
use App\Models\Ledger;
use App\Models\Group;
use App\Models\OrganizationBookParameter;
use App\Helpers\ServiceParametersHelper;
use App\Helpers\FinancialPostingHelper;
class LoanDisbursementController extends Controller
{
    public static $user_id;
    public function __construct()
    {
        self::$user_id = parent::authUserId();
    }


    public function disbursement(Request $request)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        if ($request->ajax()) {
            $loans = LoanDisbursement::withWhereHas('homeLoan.series')
                ->select('erp_loan_disbursements.*')
                ->whereIn('approvalStatus', ['Requested', 'Approved', 'approved', ConstantHelper::APPROVAL_NOT_REQUIRED, 'Rejected', 'Assessed', 'Disbursed'])
                ->where('loan_amount', '!=', null)
                ->orderBy('id', 'desc');

            if ($request->ledger || $request->type || $request->keyword) {
                $loans->leftJoin('erp_home_loans', 'erp_home_loans.id', '=', 'erp_loan_disbursements.home_loan_id');
            }

            if ($request->has('keyword')) {
                $keyword = trim($request->keyword);
                if ($request->ledger || $request->type || $request->keyword) {
                    $loans->where(function ($query) use ($keyword) {
                        $query->where('erp_home_loans.appli_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_loan_disbursements.disbursal_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.name', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.email', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.mobile', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.loan_amount', 'like', '%' . $keyword . '%');

                        if (strtolower($keyword) === 'home') {
                            $query->orWhere('erp_home_loans.type', 1);
                        } elseif (strtolower($keyword) === 'vehicle') {
                            $query->orWhere('erp_home_loans.type', 2);
                        } elseif (strtolower($keyword) === 'term') {
                            $query->orWhere('erp_home_loans.type', 3);
                        }
                    });
                }
            }

            if ($request->ledger) {
                $loans->where('erp_home_loans.name', 'like', '%' . $request->ledger . '%');
            }
            if ($request->type) {
                $loans->where('erp_home_loans.type', $request->type);
            }
            if ($request->status) {
                $loans->where('erp_loan_disbursements.approvalStatus', $request->status);
            }

            if ($request->process) {
                $loans->where('erp_loan_disbursements.approvalStatus', '!=', 'Disbursed');
            }
            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($dates[0]));
                $end = date('Y-m-d', strtotime($dates[1]));
                $loans->whereDate('erp_loan_disbursements.created_at', '>=', $start)->whereDate('erp_loan_disbursements.created_at', '<=', $end);
            }
            if (!empty($organization_id)) {
                $loans->where('erp_loan_disbursements.organization_id', $organization_id);
            }
            $loans = $loans->get();


            return DataTables::of($loans)
                ->addColumn('appli_no', function ($loan) {
                    return $loan->homeLoan->appli_no ? $loan->homeLoan->appli_no : '-';
                })
                ->addColumn('disbursal_no', function ($loan) {
                    return $loan->disbursal_no ? $loan->disbursal_no : '-';
                })
                ->addColumn('created_at', function ($loan) {
                    return $loan->created_at ? $loan->created_at->format('d-m-Y') : '-';
                })
                ->addColumn('name', function ($loan) {
                    return $loan->homeLoan->name ? $loan->homeLoan->name : '-';
                })
                ->addColumn('email', function ($loan) {
                    return $loan->homeLoan->email ? $loan->homeLoan->email : '-';
                })
                ->addColumn('mobile', function ($loan) {
                    return $loan->homeLoan->mobile ? $loan->homeLoan->mobile : '-';
                })
                ->addColumn('type', function ($loan) {
                    if ($loan->homeLoan->type == 1) {
                        $type = 'Home';
                    } elseif ($loan->homeLoan->type == 2) {
                        $type = 'Vehicle';
                    } else {
                        $type = 'Term';
                    }
                    return $type;
                })
                ->addColumn('loan_amount', function ($loan) {
                    return $loan->loan_amount ? Helper::formatIndianNumber($loan->loan_amount) : '-';
                })
                ->addColumn('actual_dis', function ($loan) {
                    return $loan->actual_dis ? Helper::formatIndianNumber($loan->actual_dis) : Helper::formatIndianNumber($loan->dis_amount);
                })
                ->addColumn('dis_amount', function ($loan) {

                    $totalDisAmount = LoanDisbursement::where('home_loan_id', $loan->home_loan_id)
                        ->where('id', '<=', $loan->id)
                        ->whereIn('approvalStatus', ['Requested', 'Approved', 'approved', 'Rejected', 'Assessed', 'Disbursed'])
                        ->sum(DB::raw('COALESCE(actual_dis, dis_amount)'));
                    return $totalDisAmount ? Helper::formatIndianNumber($totalDisAmount) : '-';
                })
                ->addColumn('dis_milestone', function ($loan) {

                    // $data = json_decode($loan->dis_milestone, true);
                    $data = $loan->dis_milestone;
                    $span = "";
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // It's valid JSON
                        $data = json_decode($data, true);
                        if (is_array($data) && count($data) != 0) {

                            foreach ($data as $option) {
                                $span = $span . '&nbsp;<span class="badge rounded-pill badge-light-secondary badgeborder-radius mb-50">'
                                    . (!empty($option['name']) ? $option['name'] : 'Milestone ' . $option['id'])
                                    . '</span>';
                            }
                        }
                        return $span ? $span : '-';
                    } else
                        return '<span class="badge rounded-pill badge-light-secondary badgeborder-radius mb-50">' . $loan->dis_milestone . '</span>';
                })

                ->addColumn('status', function ($loan) {
                    if ($loan->approvalStatus == "Requested")
                        return '<span class="badge rounded-pill badge-light-info badgeborder-radius">' . $loan->approvalStatus . '</span>';
                    else if ($loan->approvalStatus == 'Disbursed')
                        return '<span class="badge rounded-pill badge-light-success badgeborder-radius">' . $loan->approvalStatus . '</span>';
                    else if ($loan->approvalStatus == 'Rejected')
                        return '<span class="badge rounded-pill badge-light-danger badgeborder-radius">' . $loan->approvalStatus . '</span>';
                    else if ($loan->approvalStatus == 'Approved' || $loan->approvalStatus == 'approved' || ConstantHelper::APPROVAL_NOT_REQUIRED)
                        return '<span class="badge rounded-pill badge-light-success badgeborder-radius">' . $loan->approvalStatus . '</span>';
                    else {
                        return '<span class="badge rounded-pill badge-light-warning badgeborder-radius">' . $loan->approvalStatus . '</span>';
                    }
                })
                ->editColumn('created_at', function ($data) {
                    $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                    return $formatedDate;
                })
                ->addColumn('action', function ($data) {

                    return '<a href="' . route('loan.view-disbursement', ['id' => $data->id]) . '"><i data-feather="eye" class="me-50"></i></a>';
                })
                ->rawColumns(['status', 'action', 'dis_milestone'])
                ->make(true);
        } else {
            $loans = LoanDisbursement::withWhereHas('homeLoan.series')->where('organization_id', $organization_id)->orderBy('id', 'desc');
        }
        $customer_names = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->get();


        return view('loan.disbursement.index', compact('loans', 'customer_names'));
    }
    public function viewDisbursement($id)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        $data = LoanDisbursement::with('loanDisbursementDoc', 'payment')->find($id);



        $query = ErpLoanAppraisalDisbursal::whereHas('loanAppraisal.loan', function ($querys) use ($organization_id) {
            // Applying conditions on the related erp_home_loans model

            $querys->where('organization_id', $organization_id);
        })->with('loanAppraisal.loan');
        // dd($data->loanDisbursementDoc);

        $customers = $query->get()->unique('loanAppraisal.loan.id');



        $parentURL = "loan_disbursement";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
           return redirect() -> route('/');
       }
       $firstService = $servicesBooks['services'][0];
       $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

       $banks = Bank::where('status', 'active')->get();
        $loan = HomeLoan::find($data->home_loan_id);

        $buttons = Helper::actionButtonDisplayForLoan($data->book_id, $data->approvalStatus);

        $data->dis_milestone = json_decode($data->dis_milestone, true);

        return view('loan.disbursement.view', compact('loan', 'banks', 'data', 'customers', 'book_type', 'buttons'));
    }

    public function addDisbursement()
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }


        $disbursementIds = LoanDisbursement::whereNotNull('dis_milestone')
            ->get() // Get all LoanDisbursement records that have a non-null dis_milestone field
            ->flatMap(function ($disbursement) {
                // Check if dis_milestone is already an array
                $milestones = is_array($disbursement->dis_milestone)
                    ? $disbursement->dis_milestone // If it's already an array, use it directly
                    : json_decode($disbursement->dis_milestone, true); // If it's a JSON string, decode it

                // Extract and return ids from the decoded array
                return collect($milestones)->pluck('id');
            });
        $query = ErpLoanAppraisalDisbursal::whereHas('loanAppraisal.loan', function ($querys) use ($organization_id) {
            // Applying conditions on the related erp_home_loans model
            $querys->where('organization_id', $organization_id);
            $querys->whereNull('settle_status');
            $querys->whereIn('approvalStatus', ['legal docs','Disbursed']);
        })
            ->whereNotIn('id', $disbursementIds) // Exclude disbursement ids
            ->with('loanAppraisal.loan');


        $customers = $query->get()->unique('loanAppraisal.loan.id');




        $parentURL = "loan_disbursement";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
           return redirect() -> route('/');
       }
       $firstService = $servicesBooks['services'][0];
       $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

        return view('loan.disbursement.add', compact('customers', 'book_type'));
    }

    public function disbursementAddUpdate(Request $request)
    {

        $request->validate([
            'disbursal_no' => ['required'],
            'actual_dis' => 'required|numeric',
            'customer_contri' => 'required|numeric',
            'dis_milestone' => 'required',
            'dis_amount' => 'required'
        ], [
            'actual_dis.required' => 'The actual disbursal amount is required.',
            'actual_dis.numeric' => 'The actual disbursal amount must be a number.',
            'customer_contri.required' => 'The customer contribution is required.',
            'customer_contri.numeric' => 'The customer contribution must be a number.',
            'dis_milestone' => 'The disbursal milestone is required.',
            'dis_amount' => 'The disbursal amount is required.',
            'disbursal_no.required' => 'The Disbursal number is required.',
        ]);

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $organization_id = $organization->id;
        $group_id = $organization->group_id;
        $company_id = $organization->company_id;
        $loanable_id = Helper::getAuthenticatedUser()->auth_user_id;
        $userData = Helper::userCheck();
        $data = LoanDisbursement::create([
            'home_loan_id' => $request->customer_id ?? null,
            'book_id' => $request->book_id ?? null,
            'document_date' => Carbon::now()->format('Y-m-d'),
            'doc_number_type' => $request->input('doc_number_type'),
            // 'doc_number_type' => 123,
            'doc_reset_pattern' => $request->input('doc_reset_pattern'),
            'doc_prefix' => $request->input('doc_prefix'),
            'doc_suffix' => $request->input('doc_suffix'),
            'doc_no' => $request->input('doc_no'),
            'disbursal_no' => $request->disbursal_no ?? null,
            'customer_contri' => Helper::removeCommas($request->customer_contri) ?? 0,
            'actual_dis' => Helper::removeCommas($request->actual_dis) ?? Helper::removeCommas($request->dis_amount) ?? null,
            'dis_remarks' => $request->dis_remarks ?? null,
            'dis_amount' => Helper::removeCommas($request->dis_amount) ?? null,
            'dis_milestone' => $request->milestone_json ?? null,
            'approvalStatus' => "Requested",
            'approvalLevel'=>1,
            'organization_id' => $organization_id,
            'loan_amount' => Helper::removeCommas($request->loan_amount) ?? null,
            'loanable_id' => $loanable_id,
            'loanable_type' => $userData['user_type'],
            'group_id' => $group_id,
            'company_id' => $company_id
        ]);

        /*if ($request->has('disbursal_id_data')) {
            $dLoan = DisbursalLoan::where('id', $request->disbursal_id_data)->first();
            $dLoan->status = 1;
            $dLoan->save();
        }*/

        $file_Path = [];
        if ($request->hasFile('disbursement_docs')) {
            $files = $request->file('disbursement_docs');

            foreach ($files as $file) {
                $filePath = $file->store('loan_documents', 'public');

                LoanDisbursementDoc::create([
                    'loan_disbursement_id' => $data->id,
                    'doc' => $filePath
                ]);
                $file_Path[] = $filePath;
            }
        }

        $loan_type = explode(' ', $request->loan_type);
        $loanApplicationLog = LoanApplicationLog::create([
            'loan_application_id' => $request->customer_id,
            'loan_type' => strtolower($loan_type[0]),
            'action_type' => 'disbursement',
            'user_id' => self::$user_id,
            'remarks' => null
        ]);

        $loan_data = LoanDisbursement::with('homeLoan', 'loanDisbursementDoc')->find($data->id);

        Helper::logs(
            $loan_data->homeLoan->series,
            $loan_data->homeLoan->appli_no,
            $loan_data->homeLoan->id,
            $loan_data->organization_id,
            'Loan Disbursement Request',
            $request->dis_remarks ?? '-',
            $loan_data->loanable_id,
            $file_Path,
            $loan_data->loanable_type,
            0,
            $loan_data->created_at,
            $loan_data->approvalStatus
        );

        $organization = Organization::getOrganization();
        $book_type = (int) $request->book_id;
        if ($organization) {
            NumberPattern::incrementIndex($organization->id, $book_type);
        }

        return redirect()->route('loan.disbursement')->with('success', 'Disbursement Added Successfully!');
    }

    public function disbursement_assesment(Request $request)
    {

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;

        }

        if ($request->ajax()) {
            $loans = LoanDisbursement::withWhereHas('homeLoan.series')->select('erp_loan_disbursements.*')
                ->where('approvalStatus', 'Requested')
                ->where('loan_amount', '!=', null)
                ->orderBy('id', 'desc');

            if ($request->ledger || $request->type || $request->keyword) {
                $loans->leftJoin('erp_home_loans', 'erp_home_loans.id', '=', 'erp_loan_disbursements.home_loan_id');
            }

            if ($request->has('keyword')) {
                $keyword = trim($request->keyword);
                if ($request->ledger || $request->type || $request->keyword) {
                    $loans->where(function ($query) use ($keyword) {
                        $query->where('erp_home_loans.appli_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_loan_disbursements.disbursal_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.name', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.email', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.mobile', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.loan_amount', 'like', '%' . $keyword . '%');

                        if (strtolower($keyword) === 'home') {
                            $query->orWhere('erp_home_loans.type', 1);
                        } elseif (strtolower($keyword) === 'vehicle') {
                            $query->orWhere('erp_home_loans.type', 2);
                        } elseif (strtolower($keyword) === 'term') {
                            $query->orWhere('erp_home_loans.type', 3);
                        }
                    });
                }
            }

            if ($request->ledger) {
                $loans->where('erp_home_loans.name', 'like', '%' . $request->ledger . '%');
            }
            if ($request->type) {
                $loans->where('erp_home_loans.type', $request->type);
            }
            if ($request->process) {
                $loans->where('erp_loan_disbursements.approvalStatus', '!=', 'Disbursed');
            }
            if ($request->status) {
                $loans->where('erp_loan_disbursements.approvalStatus', $request->status);
            }

            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($dates[0]));
                $end = date('Y-m-d', strtotime($dates[1]));
                $loans->whereDate('erp_loan_disbursements.created_at', '>=', $start)->whereDate('erp_loan_disbursements.created_at', '<=', $end);
            }
            if (!empty($organization_id)) {
                $loans->where('erp_loan_disbursements.organization_id', $organization_id);
            }
            $loans = $loans->get();

            return DataTables::of($loans)
                ->addColumn('appli_no', function ($loan) {
                    return $loan->homeLoan->appli_no ? $loan->homeLoan->appli_no : '-';
                })
                ->addColumn('disbursal_no', function ($loan) {
                    return $loan->disbursal_no ? $loan->disbursal_no : '-';
                })
                ->addColumn('created_at', function ($loan) {
                    return $loan->created_at ? $loan->created_at->format('d-m-Y') : '-';
                })
                ->addColumn('name', function ($loan) {
                    return $loan->homeLoan->name ? $loan->homeLoan->name : '-';
                })
                ->addColumn('email', function ($loan) {
                    return $loan->homeLoan->email ? $loan->homeLoan->email : '-';
                })
                ->addColumn('mobile', function ($loan) {
                    return $loan->homeLoan->mobile ? $loan->homeLoan->mobile : '-';
                })
                ->addColumn('type', function ($loan) {
                    if ($loan->homeLoan->type == 1) {
                        $type = 'Home';
                    } elseif ($loan->homeLoan->type == 2) {
                        $type = 'Vehicle';
                    } else {
                        $type = 'Term';
                    }
                    return $type;
                })
                ->addColumn('loan_amount', function ($loan) {
                    return $loan->loan_amount ? Helper::formatIndianNumber($loan->loan_amount) : '-';
                })
                ->addColumn('actual_dis', function ($loan) {
                    return $loan->actual_dis ? Helper::formatIndianNumber($loan->actual_dis) : '-';
                })
                ->addColumn('dis_amount', function ($loan) {
                    $totalDisAmount = LoanDisbursement::where('home_loan_id', $loan->home_loan_id)
                        ->where('id', '<=', $loan->id)
                        ->whereIn('approvalStatus', ['Requested', 'Approved', 'approved', ConstantHelper::APPROVAL_NOT_REQUIRED, 'Rejected', 'Assessed', 'Disbursed'])
                        ->sum(DB::raw('COALESCE(actual_dis, dis_amount)'));
                    return $totalDisAmount ? Helper::formatIndianNumber($totalDisAmount) : '-';
                })
                ->addColumn('dis_milestone', function ($loan) {

                    $data = json_decode($loan->dis_milestone, true);
                    $span = "";
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // It's valid JSON
                        if (is_array($data) && count($data) != 0) {

                            foreach ($data as $option) {
                                $span = $span . '&nbsp;<span class="badge rounded-pill badge-light-secondary badgeborder-radius mb-50">' . $option['name'] . '</span>'; // Outputs each milestone name one by one
                            }
                        }
                        return $span ? $span : '-';
                    } else
                        return '<span class="badge rounded-pill badge-light-secondary badgeborder-radius mb-50">' . $loan->dis_milestone . '</span>';
                })

                ->editColumn('created_at', function ($data) {
                    $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                    return $formatedDate;
                })
                ->addColumn('action', function ($data) {

                    return '<a href="' . route('loan.view-disbursement-assesment', ['id' => $data->id]) . '"><i data-feather="eye" class="me-50"></i></a>';
                })
                ->rawColumns(['status', 'action', 'dis_milestone'])
                ->make(true);
        } else {
            $loans = LoanDisbursement::withWhereHas('homeLoan.series')->where('organization_id', $organization_id)
                ->where('approvalStatus', 'Requested')
                ->orderBy('id', 'desc');
        }
        $customer_names = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->get();

        return view('loan.disbursement.assesment', compact('loans', 'customer_names'));
    }
    public function viewDisbursementAssesment($id)
    {

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        $data = LoanDisbursement::with('loanDisbursementDoc')->find($id);


        $query = ErpLoanAppraisalDisbursal::whereHas('loanAppraisal.loan', function ($querys) use ($organization_id) {
            // Applying conditions on the related erp_home_loans model

            $querys->where('organization_id', $organization_id);
        })->with('loanAppraisal.loan');
        // dd($data->loanDisbursementDoc);

        $customers = $query->get()->unique('loanAppraisal.loan.id');

        $parentURL = "loan_disbursement";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
           return redirect() -> route('/');
       }
       $firstService = $servicesBooks['services'][0];
       $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

        $loan = HomeLoan::find($data->home_loan_id);
        $buttons = Helper::actionButtonDisplayForLoan($data->book_id, $data->approvalStatus);
        // dd($buttons);

        return view('loan.disbursement.view_assesment', compact('loan', 'data', 'customers', 'book_type', 'buttons'));
    }

    public function disbursement_approval(Request $request)
    {

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        if ($request->ajax()) {
            $loans = LoanDisbursement::withWhereHas('homeLoan.series')->select('erp_loan_disbursements.*')
                ->where('approvalStatus', 'Assessed')
                ->where('loan_amount', '!=', null)
                ->orderBy('id', 'desc');

            if ($request->ledger || $request->type || $request->keyword) {
                $loans->leftJoin('erp_home_loans', 'erp_home_loans.id', '=', 'erp_loan_disbursements.home_loan_id');
            }

            if ($request->has('keyword')) {
                $keyword = trim($request->keyword);
                if ($request->ledger || $request->type || $request->keyword) {
                    $loans->where(function ($query) use ($keyword) {
                        $query->where('erp_home_loans.appli_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_loan_disbursements.disbursal_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.name', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.email', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.mobile', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.loan_amount', 'like', '%' . $keyword . '%');

                        if (strtolower($keyword) === 'home') {
                            $query->orWhere('erp_home_loans.type', 1);
                        } elseif (strtolower($keyword) === 'vehicle') {
                            $query->orWhere('erp_home_loans.type', 2);
                        } elseif (strtolower($keyword) === 'term') {
                            $query->orWhere('erp_home_loans.type', 3);
                        }
                    });
                }
            }

            if ($request->ledger) {
                $loans->where('erp_home_loans.name', 'like', '%' . $request->ledger . '%');
            }
            if ($request->type) {
                $loans->where('erp_home_loans.type', $request->type);
            }
            if ($request->status) {
                $loans->where('erp_loan_disbursements.approvalStatus', $request->status);
            }
            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($dates[0]));
                $end = date('Y-m-d', strtotime($dates[1]));
                $loans->whereDate('erp_loan_disbursements.created_at', '>=', $start)->whereDate('erp_loan_disbursements.created_at', '<=', $end);
            }
            if (!empty($organization_id)) {
                $loans->where('erp_loan_disbursements.organization_id', $organization_id);
            }
            $loans = $loans->get();

            return DataTables::of($loans)
                ->addColumn('appli_no', function ($loan) {
                    return $loan->homeLoan->appli_no ? $loan->homeLoan->appli_no : '-';
                })
                ->addColumn('disbursal_no', function ($loan) {
                    return $loan->disbursal_no ? $loan->disbursal_no : '-';
                })
                ->addColumn('created_at', function ($loan) {
                    return $loan->created_at ? $loan->created_at->format('d-m-Y') : '-';
                })
                ->addColumn('name', function ($loan) {
                    return $loan->homeLoan->name ? $loan->homeLoan->name : '-';
                })
                ->addColumn('email', function ($loan) {
                    return $loan->homeLoan->email ? $loan->homeLoan->email : '-';
                })
                ->addColumn('mobile', function ($loan) {
                    return $loan->homeLoan->mobile ? $loan->homeLoan->mobile : '-';
                })
                ->addColumn('type', function ($loan) {
                    if ($loan->homeLoan->type == 1) {
                        $type = 'Home';
                    } elseif ($loan->homeLoan->type == 2) {
                        $type = 'Vehicle';
                    } else {
                        $type = 'Term';
                    }
                    return $type;
                })
                ->addColumn('loan_amount', function ($loan) {
                    return $loan->loan_amount ? Helper::formatIndianNumber($loan->loan_amount) : '-';
                })
                ->addColumn('actual_dis', function ($loan) {
                    return $loan->actual_dis ? Helper::formatIndianNumber($loan->actual_dis) : '-';
                })
                ->addColumn('dis_amount', function ($loan) {
                    $totalDisAmount = LoanDisbursement::where('home_loan_id', $loan->home_loan_id)
                        ->where('id', '<=', $loan->id)
                        ->whereIn('approvalStatus', ['Requested', 'Approved', ConstantHelper::APPROVAL_NOT_REQUIRED, 'approved', 'Rejected', 'Assessed', 'Disbursed'])
                        ->sum(DB::raw('COALESCE(actual_dis, dis_amount)'));
                    return $totalDisAmount ? Helper::formatIndianNumber($totalDisAmount) : '-';
                })
                ->addColumn('dis_milestone', function ($loan) {

                    $data = json_decode($loan->dis_milestone, true);
                    $span = "";
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // It's valid JSON
                        if (is_array($data) && count($data) != 0) {

                            foreach ($data as $option) {
                                $span = $span . '&nbsp;<span class="badge rounded-pill badge-light-secondary badgeborder-radius mb-50">' . $option['name'] . '</span>'; // Outputs each milestone name one by one
                            }
                        }
                        return $span ? $span : '-';
                    } else
                        return '<span class="badge rounded-pill badge-light-secondary badgeborder-radius mb-50">' . $loan->dis_milestone . '</span>';
                })

                ->editColumn('created_at', function ($data) {
                    $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                    return $formatedDate;
                })
                ->addColumn('action', function ($data) {

                    return '<a href="' . route('loan.view-disbursement-approval', ['id' => $data->id]) . '"><i data-feather="eye" class="me-50"></i></a>';
                })
                ->rawColumns(['status', 'action', 'dis_milestone'])
                ->make(true);
        } else {
            $loans = LoanDisbursement::withWhereHas('homeLoan.series')->where('organization_id', $organization_id)
                ->where('approvalStatus', 'Requested')
                ->orderBy('id', 'desc');
        }
        $customer_names = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->get();

        return view('loan.disbursement.approval', compact('loans', 'customer_names'));
    }
    public function viewDisbursementApproval($id)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        $data = LoanDisbursement::with('loanDisbursementDoc')->find($id);


        $query = ErpLoanAppraisalDisbursal::whereHas('loanAppraisal.loan', function ($querys) use ($organization_id) {
            // Applying conditions on the related erp_home_loans model

            $querys->where('organization_id', $organization_id);
        })->with('loanAppraisal.loan');
        // dd($data->loanDisbursementDoc);

        $customers = $query->get()->unique('loanAppraisal.loan.id');

        $parentURL = "loan_disbursement";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
           return redirect() -> route('/');
       }
       $firstService = $servicesBooks['services'][0];
       $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

       $loan = HomeLoan::find($data->home_loan_id);
        $buttons = Helper::actionButtonDisplayForLoan($data->book_id, $data->approvalStatus, $data->id, $data->loan_amount);
        // dd($buttons);
        return view('loan.disbursement.view_approval', compact('loan', 'data', 'customers', 'book_type', 'buttons'));
    }


    public function disbursement_submission(Request $request)
    {

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        if ($request->ajax()) {
            $loans = LoanDisbursement::withWhereHas('homeLoan.series')->select('erp_loan_disbursements.*')
                ->where('approvalStatus', 'Approved')
                ->orWhere('approvalStatus', ConstantHelper::APPROVAL_NOT_REQUIRED)
                ->orWhere('approvalStatus', 'approved')
                ->where('loan_amount', '!=', null)
                ->orderBy('id', 'desc');

            if ($request->ledger || $request->type || $request->keyword) {
                $loans->leftJoin('erp_home_loans', 'erp_home_loans.id', '=', 'erp_loan_disbursements.home_loan_id');
            }

            if ($request->has('keyword')) {
                $keyword = trim($request->keyword);
                if ($request->ledger || $request->type || $request->keyword) {
                    $loans->where(function ($query) use ($keyword) {
                        $query->where('erp_home_loans.appli_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_loan_disbursements.disbursal_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.name', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.email', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.mobile', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.loan_amount', 'like', '%' . $keyword . '%');

                        if (strtolower($keyword) === 'home') {
                            $query->orWhere('erp_home_loans.type', 1);
                        } elseif (strtolower($keyword) === 'vehicle') {
                            $query->orWhere('erp_home_loans.type', 2);
                        } elseif (strtolower($keyword) === 'term') {
                            $query->orWhere('erp_home_loans.type', 3);
                        }
                    });
                }
            }

            if ($request->ledger) {
                $loans->where('erp_home_loans.name', 'like', '%' . $request->ledger . '%');
            }
            if ($request->type) {
                $loans->where('erp_home_loans.type', $request->type);
            }
            if ($request->status) {
                $loans->where('erp_loan_disbursements.approvalStatus', $request->status);
            }
            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($dates[0]));
                $end = date('Y-m-d', strtotime($dates[1]));
                $loans->whereDate('erp_loan_disbursements.created_at', '>=', $start)->whereDate('erp_loan_disbursements.created_at', '<=', $end);
            }
            if (!empty($organization_id)) {
                $loans->where('erp_loan_disbursements.organization_id', $organization_id);
            }
            $loans = $loans->get();


            return DataTables::of($loans)
                ->addColumn('appli_no', function ($loan) {
                    return $loan->homeLoan->appli_no ? '<strong>' . $loan->homeLoan->appli_no . '</strong>' : '-';
                })
                ->addColumn('disbursal_no', function ($loan) {
                    return $loan->disbursal_no ? '<strong>' . $loan->disbursal_no . '</strong>' : '-';
                })
                ->addColumn('created_at', function ($loan) {
                    return $loan->created_at ? $loan->created_at->format('d-m-Y') : '-';
                })
                ->addColumn('name', function ($loan) {
                    return $loan->homeLoan->name ? $loan->homeLoan->name : '-';
                })
                ->addColumn('email', function ($loan) {
                    return $loan->homeLoan->email ? $loan->homeLoan->email : '-';
                })
                ->addColumn('mobile', function ($loan) {
                    return $loan->homeLoan->mobile ? $loan->homeLoan->mobile : '-';
                })
                ->addColumn('type', function ($loan) {
                    if ($loan->homeLoan->type == 1) {
                        $type = 'Home';
                    } elseif ($loan->homeLoan->type == 2) {
                        $type = 'Vehicle';
                    } else {
                        $type = 'Term';
                    }
                    return $type;
                })
                ->addColumn('loan_amount', function ($loan) {
                    return $loan->loan_amount ? Helper::formatIndianNumber($loan->loan_amount) : '-';
                })
                ->addColumn('actual_dis', function ($loan) {
                    return $loan->actual_dis ? Helper::formatIndianNumber($loan->actual_dis) : '-';
                })
                ->addColumn('dis_amount', function ($loan) {
                    $totalDisAmount = LoanDisbursement::where('home_loan_id', $loan->home_loan_id)
                        ->where('id', '<=', $loan->id)
                        ->whereIn('approvalStatus', ['Requested', 'Approved', 'approved', 'Rejected', 'Assessed', 'Disbursed'])
                        ->sum(DB::raw('COALESCE(actual_dis, dis_amount)'));
                    return $totalDisAmount ? Helper::formatIndianNumber($totalDisAmount) : '-';
                })
                ->addColumn('dis_milestone', function ($loan) {

                    // $data = json_decode($loan->dis_milestone, true);
                    $data = $loan->dis_milestone;
                    $span = "";
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // It's valid JSON
                        $data = json_decode($data, true);
                        if (is_array($data) && count($data) != 0) {

                            foreach ($data as $option) {
                                $span = $span . '&nbsp;<span class="badge rounded-pill badge-light-secondary badgeborder-radius mb-50">' . $option['name'] . '</span>'; // Outputs each milestone name one by one
                            }
                        }
                        return $span ? $span : '-';
                    } else
                        return '<span class="badge rounded-pill badge-light-secondary badgeborder-radius mb-50">' . $loan->dis_milestone . '</span>';
                })

                ->editColumn('created_at', function ($data) {
                    $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                    return $formatedDate;
                })
                ->addColumn('action', function ($data) {

                    return '<a href="' . route('loan.view-disbursement-submission', ['id' => $data->id]) . '"><i data-feather="eye" class="me-50"></i></a>';
                })
                ->rawColumns(['status', 'action', 'dis_milestone', 'appli_no', 'disbursal_no'])
                ->make(true);
        } else {
            $loans = LoanDisbursement::withWhereHas('homeLoan.series')->where('organization_id', $organization_id)
                ->where('approvalStatus', 'Requested')
                ->orderBy('id', 'desc');
        }
        $customer_names = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->get();

        return view('loan.disbursement.submission', compact('loans', 'customer_names'));
    }
    public function viewDisbursementSubmssion($id)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        $data = LoanDisbursement::with('loanDisbursementDoc')->find($id);


        $query = ErpLoanAppraisalDisbursal::whereHas('loanAppraisal.loan', function ($querys) use ($organization_id) {
            // Applying conditions on the related erp_home_loans model

            $querys->where('organization_id', $organization_id);
        })->with('loanAppraisal.loan');
        // dd($data->loanDisbursementDoc);

        $customers = $query->get()->unique('loanAppraisal.loan.id');

        $banks = Bank::where('status', 'active')->get();


        $parentURL = "loan_disbursement";

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
           return redirect() -> route('/');
       }
       $firstService = $servicesBooks['services'][0];
       $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();

       $loan = HomeLoan::find($data->home_loan_id);
        $buttons = Helper::actionButtonDisplayForLoan($data->book_id, $data->approvalStatus, $data->home_loan_id, $data->loan_amount);
        // dd($buttons);
        $groupId = Group::where('name', 'Cash-in-Hand')->value('id');

        $ledgers = Ledger::withDefaultGroupCompanyOrg()
            ->where(function ($query) use ($groupId) {
                $query->whereJsonContains('ledger_group_id',(string) $groupId)
                    ->orWhere('ledger_group_id', $groupId);
            })->get();

        $isPostingRequired = false;
        $postingRequiredParam = OrganizationBookParameter::where('book_id', $data->book_id)
        ->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
        if (isset($postingRequiredParam))
        {
            $isPostingRequired = ($postingRequiredParam -> parameter_value[0] ?? '') === "yes" ? true : false;
        }
        return view('loan.disbursement.view_submission', compact('loan', 'banks', 'data', 'customers', 'book_type', 'buttons','ledgers','isPostingRequired'));
    }

    public function loanGetDisbursCustomer(Request $request)
    {
        $loan_type = $request->query('loanType');
        $customer_name = $request->query('customerName');
        //$appli_no = $request->query('appliNo');

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        // Step 1: Extract all ids from the dis_milestone field in LoanDisbursement
        $disbursementIds = $disbursementIds = LoanDisbursement::whereNotNull('dis_milestone')
            ->get() // Get all LoanDisbursement records that have a non-null dis_milestone field
            ->flatMap(function ($disbursement) {
                // Check if dis_milestone is already an array
                $milestones = is_array($disbursement->dis_milestone)
                    ? $disbursement->dis_milestone // If it's already an array, use it directly
                    : json_decode($disbursement->dis_milestone, true); // If it's a JSON string, decode it

                // Extract and return ids from the decoded array
                return collect($milestones)->pluck('id');
            });

        // Step 2: Modify the main query to add whereNotIn condition
        $query = ErpLoanAppraisalDisbursal::whereHas('loanAppraisal.loan', function ($querys) use ($organization_id, $loan_type, $customer_name) {
            // Applying conditions on the related erp_home_loans model
            $querys->where('organization_id', $organization_id);
            $querys->whereNull('settle_status');
            $querys->whereIn('approvalStatus', ['legal docs','Disbursed']);
            if ($loan_type) {
                $querys->where('type', $loan_type);
            }
            if ($customer_name) {
                $querys->where('name', 'like', "%{$customer_name}%");
            }
        });
        if ($request->filter_form == "add")
            $query = $query->whereNotIn('id', $disbursementIds); // Exclude disbursement ids

        $query = $query->with('loanAppraisal.loan');



        // Step 3: Get unique customers
        $customers = $query->get()->unique(function ($item) {
            return $item->loanAppraisal->loan->id; // Ensure uniqueness based on loanAppraisal.loan.id
        });

        // Return the result as JSON
        return response()->json(['customers' => $customers]);
    }

    public function get_bank_details(Request $request)
    {
        $id = $request->bank_id;
        $bank = Bank::find($id)->bankDetails;
        return response()->json($bank);
    }

    public function DisApprReject(Request $request)
    {
        if (empty($request->checkedData)) {
            return redirect("loan/disbursement")->with('error', 'No Data found for Approve/Reject');
        }
        $app_rej = $request->checkedData;

        $multi_files = [];
        if ($request->hasFile('dis_appr_doc') && !$request->has('store_dis_appr_doc')) {
            if ($request->hasFile('dis_appr_doc')) {
                $files = $request->file('dis_appr_doc');
                foreach ($files as $file) {
                    $filePath = $file->store('loan_documents', 'public');
                    $multi_files[] = $filePath;
                }
            }
            $data = LoanDisbursement::updateOrCreate([
                'id' => $app_rej
            ], [
                'status' => $request->dis_appr_status ?? null,
                'dis_appr_doc' => (count($multi_files) > 0) ? json_encode($multi_files) : '[]',
                'dis_appr_remark' => $request->dis_appr_remark ?? null
            ]);
        } else {
            $store_dis_appr_docData = $request->store_dis_appr_doc;
            $data = LoanDisbursement::updateOrCreate([
                'id' => $app_rej
            ], [
                'status' => $request->dis_appr_status ?? null,
                'dis_appr_doc' => $store_dis_appr_docData,
                'dis_appr_remark' => $request->dis_appr_remark ?? null
            ]);
        }

        if ($request->dis_appr_status == 2) {
            return redirect("loan/disbursement")->with('success', 'Approved Successfully!');
        } else {
            return redirect("loan/disbursement")->with('success', 'Rejected Successfully!');
        }
    }
    public function disbursement_payment(Request $request)
    {

        $id = $request->id;

        $data = LoanDisbursement::find($id);
        try {

            $isPostingRequired = false;
            $postingRequiredParam = OrganizationBookParameter::where('book_id', $data->book_id)
            ->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
            if (isset($postingRequiredParam))
            {
                $isPostingRequired = ($postingRequiredParam -> parameter_value[0] ?? '') === "yes" ? true : false;
            }

            if($isPostingRequired)
            {
                $requestdata = $request->all();
                return response() -> json([
                    'status' => 'success',
                    'requestdata' => json_encode($requestdata),
                ]);
            }
            else
            {
                $data->payment_date = $request->payment_date;
                $data->bank_details_id = $request->account_number;
                $data->payment_mode = $request->payment_mode;
                $data->payment_ref_no = $request->payment_ref_no;
                $data->customer_account_number = $request->customer_account_number;
                $data->customer_bank_name = $request->customer_bank_name;
                $data->approvalStatus = 'Disbursed';
                $data->save();

                $loan = HomeLoan::find($data->home_loan_id);
                $loan->approvalStatus = 'Disbursed';
                $loan->save();
                return redirect(route('loan.disbursement.submission'))->with('success', 'Disbursed Succesfully');
            }

        } catch (\Exception $e) {
            return redirect(route('loan.view-disbursement-submission', ['id' => $id]))->with('error', $e->getMessage());
        }
    }
    public function proceedDisbursementAssesment(Request $request)
    {
        if ($request->status == "Assessed") {
            try {

                $id = $request->id;
                $filePath = "";
                $file_path = [];
                if ($request->hasFile('approve_doc')) {
                    $files = $request->file('approve_doc');
                    $filePath = $files->store('loan_assess_disbursement', 'public');
                    $file_path[] = $filePath;
                }

                $assessed = 'Assessed';
                $data = LoanDisbursement::find($id);
                $status = ConstantHelper::ASSESSED ? Helper::checkApprovalLoanRequired($data->book_id) : $assessed;

                $data->customer_contri = $request->customer_contribution ?? 0;
                $data->actual_dis = Helper::removeCommas($request->disbursal_amount) ?? Helper::removeCommas($data->dis_amount) ?? null;
                $data->assess_doc = $filePath;
                $data->assess_remarks = $request->approve_remarks;
                $data->approvalStatus = $status;
                $data->save();






                $loan_data = LoanDisbursement::with('homeLoan', 'loanDisbursementDoc')->find($data->id);
                if($status==ConstantHelper::ASSESSED){
                    if ($loan_data->approvelworkflow->count() > 0) { // Check if the relationship has records
                        foreach ($loan_data->approvelworkflow as $approver) {
                            if ($approver->user) {
                                $created_by = $loan_data->loanable_id;
                                $creator = AuthUser::find($created_by);
                                LoanNotificationController::notifyLoanDisbursSubmission($creator, $loan_data);
                            }
                        }
                    }
                }



                Helper::logs(
                    $loan_data->homeLoan->series,
                    $loan_data->homeLoan->appli_no,
                    $loan_data->homeLoan->id,
                    $loan_data->organization_id,
                    'Loan Assessment',
                    $request->approve_remarks ?? '-',
                    $loan_data->loanable_id,
                    $file_path,
                    $loan_data->loanable_type,
                    0,
                    $data->created_at,
                    $request->status
                );

                return redirect(route('loan.disbursement.assesment'))->with('success', 'Disbursement Proceed');
            } catch (\Exception $e) {
                return redirect(route('loan.view-disbursement-assesment', ['id' => $id]))->with('error', $e->getMessage());
            }
        } else if ($request->status == "Approved" || $request->status == "approved") {
            try {

                $id = $request->id;
                $filePath = "";
                $file_path = [];
                if ($request->hasFile('approve_doc')) {

                    $files = $request->file('approve_doc');
                    $filePath = $files->store('loan_approve_disbursement', 'public');
                    $file_path[] = $filePath;
                }
                //dd($filePath);



                $data = LoanDisbursement::find($id);
                $creator_type = $data->loanable_type;
                $created_by = $data->loanable_id;
                $creator = AuthUser::find($created_by);

//                if ($creator_type != null) {
//                    switch ($creator_type) {
//                        case 'employee':
//                            $creator = Employee::find($created_by);
//                            break;
//
//                        case 'user':
//                            $creator = User::find($created_by);
//                            break;
//
//                        default:
//                            $creator = $creator_type::find($created_by);
//                            break;
//                    }
//                }

                $approveDocument = Helper::approveDocument($data->book_id, $data->id, 0 ,$request->approve_remarks, $file_path, $data->approvalLevel, "approve");


                $data->customer_contri = $request->customer_contribution ?? 0;
                $data->actual_dis = Helper::removeCommas($request->disbursal_amount) ?? Helper::removeCommas($data->dis_amount) ?? null;
                $data->approve_doc = $filePath;
                $data->approvalLevel = $approveDocument['nextLevel'];
                $data->approve_remarks = $request->approve_remarks;
                $data->approvalStatus = $approveDocument['approvalStatus'];
                $data->save();
//                dd($creator);
                LoanNotificationController::notifyLoanDisbursApproved($creator, $data);


                $loan_data = LoanDisbursement::with('homeLoan', 'loanDisbursementDoc')->find($data->id);


                Helper::logs(
                    $loan_data->homeLoan->series,
                    $loan_data->homeLoan->appli_no,
                    $loan_data->homeLoan->id,
                    $loan_data->organization_id,
                    'Loan Assessment',
                    $request->approve_remarks ?? '-',
                    $loan_data->loanable_id,
                    $file_path,
                    $loan_data->loanable_type,
                    0,
                    $data->created_at,
                    $request->status
                );

                return redirect(route('loan.disbursement.approval'))->with('success', 'Disbursement Proceed');
            } catch (\Exception $e) {
                return redirect(route('loan.view-disbursement-approval', ['id' => $id]))->with('error', $e->getMessage());
            }
        }
    }
    public function rejectDisbursementAssesment(Request $request)
    {
        try {

            $id = $request->id;
            $filePath = "";
            if ($request->hasFile('reject_doc')) {
                $files = $request->file('reject_doc');
                $filePath = $files->store('loan_reject_disbursement', 'public');
            }


            $data = LoanDisbursement::find($id);
            $approveDocument = Helper::approveDocument($data->book_id, $data->id, 0 ,$request->approve_remarks, $filePath, $data->approvalLevel, "reject");

            $data->reject_doc = $filePath;
            $data->reject_remarks = $request->reject_remarks;
            $data->approvalStatus = $approveDocument['approvalStatus'];
            $data->save();
            $creator_type = $data->loanable_type;
                $created_by = $data->loanable_id;
                $creator = AuthUser::find($created_by);
//                $creator = null;
//
//                if ($creator_type != null) {
//                    switch ($creator_type) {
//                        case 'employee':
//                            $creator = Employee::find($created_by);
//                            break;
//
//                        case 'user':
//                            $creator = User::find($created_by);
//                            break;
//
//                        default:
//                            $creator = $creator_type::find($created_by);
//                            break;
//                    }
//                }

            LoanNotificationController::notifyLoanDisbursReject($creator, $data);

            return redirect(route('loan.disbursement.assesment'))->with('success', 'Disbursement Rejected');
        } catch (\Exception $e) {
            return redirect(route('loan.view-disbursement-assesment', ['id' => $id]))->with('error', $e->getMessage());
        }
    }
    public function loanGetCustomer(Request $request)
    {
        $id = $request->id;
        $query = ErpLoanAppraisalDisbursal::whereHas('loanAppraisal.loan')->with('loanAppraisal.loan');


        $customer_record = $query->get()->unique('loanAppraisal.loan.id')
            ->where('loanAppraisal.loan.id', $id)->first();


        return response()->json(['customer_record' => $customer_record]);
    }

    public function getPostingDetails(Request $request)
    {
        try {
            $data = FinancialPostingHelper::disVoucherPosting($request -> book_id ?? 0, $request -> document_id ?? 0, "get",$request->remakrs);
            return response() -> json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch(Exception $ex) {
            return response() -> json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex -> getMessage()
            ]);
        }
    }

    public function postInvoice(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = FinancialPostingHelper::disVoucherPosting($request -> book_id ?? 0, $request -> document_id ?? 0, "post");
            if ($data['status'])
            {
                DB::commit();
            }
            else
            {
                DB::rollBack();
                return response() -> json([
                    'status' => 'error',
                    'message' => $data['message'],
                    'error' => $data['message']
                ]);

            }

            $id = $request -> document_id;
            $data1 = json_decode($request->data1);


            $data = LoanDisbursement::find($id);

            $data->payment_date = $request->payment_date;
            $data->bank_details_id = $request->account_number;
            $data->payment_mode = $request->payment_mode;
            $data->payment_ref_no = $request->payment_ref_no;
            $data->customer_account_number = $request->customer_account_number;
            $data->customer_bank_name = $request->customer_bank_name;
            $data->approvalStatus = 'Disbursed';
            $data->save();

            $loan = HomeLoan::find($data->home_loan_id);
            $loan->approvalStatus = 'Disbursed';
            $loan->save();

            return response() -> json([
                'status' => 'success',
                'data' => $data
            ]);

        } catch(Exception $ex) {
            DB::rollBack();
            return response() -> json([
                'status' => false,
                'message' => 'Some internal error occured',
                'error' => $ex -> getMessage()
            ]);
        }
    }
}
