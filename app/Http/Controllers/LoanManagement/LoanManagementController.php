<?php

namespace App\Http\Controllers\LoanManagement;

use Carbon\Carbon;
use App\Models\Book;
use App\Helpers\Helper;
use App\Models\HomeLoan;
use App\Rules\ValidAppliNo;
use App\Models\InterestRate;
use App\Models\Organization;
use App\Models\RecoveryLoan;
use Illuminate\Http\Request;
use App\Models\DisbursalLoan;
use App\Models\NumberPattern;
use App\Models\LoanManagement;
use App\Models\LoanSettlement;
use App\Helpers\ConstantHelper;
use App\Models\RecoveryLoanDoc;
use App\Models\LoanDisbursement;
use App\Models\ErpLoanAppraisal;
use App\Models\ErpLoanAppraisalDisbursal;
use App\Mail\LoanUserMessageMail;
use App\Models\InterestRateScore;
use App\Models\LoanSettlementDoc;
use App\Models\LoanApplicationLog;
use Illuminate\Support\Facades\DB;
use App\Models\LoanDisbursementDoc;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\RecoveryScheduleLoan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\LoanSettlementSchedule;
use Yajra\DataTables\Facades\DataTables;

class LoanManagementController extends Controller
{
    public static $user_id;
    public function __construct()
    {
        self::$user_id = parent::authUserId();
    }

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
            $loans = HomeLoan::with('settlement', 'loanDisbursements', 'loanAppraisal')->whereHas('series')->orderBy('id', 'desc');
            // $loans = HomeLoan::with('settlement')
            //     ->whereHas('settlement') // Ensures only loans with settlement data are fetched
            //     ->orderBy('id', 'desc');

            // Apply status filters if status is present
            if ($request->status) {
                switch ($request->status) {
                    case 'under processing':
                        $loans->whereNull('settle_status') // Only fetch records with settle_status = NULL
                            ->where(function ($query) {
                                $query->where('approvalStatus', 'Draft')
                                    ->orWhere('approvalStatus', 'draft')
                                    ->orWhere('approvalStatus', 'appraisal')
                                    ->orWhere('approvalStatus', 'submitted')
                                    ->orWhere('approvalStatus', 'assessment')
                                    ->orWhere('approvalStatus', 'Assessed')
                                    ->orWhere('approvalStatus', 'approval')
                                    ->orWhere('approvalStatus', 'legal')
                                    ->orWhere('approvalStatus', 'sanctioned')
                                    ->orWhere('approvalStatus', 'processingfee')
                                    ->orWhere('approvalStatus', 'approved')
                                    ->orWhere('approvalStatus', 'partially_approved')
                                    ->orWhere('approvalStatus', 'approval_not_required')
                                    ->orWhere('approvalStatus', 'legal docs');
                            });
                        break;

                    case 'active':
                        $loans->whereNull('settle_status')
                            ->where(function ($query) {
                                $query->where('approvalStatus', 'Disbursed')
                                    ->orWhere('approvalStatus', 'Disbursed')
                                    ->orWhere('approvalStatus', 'recovery');
                            });
                        break;

                    case 'under settlement':
                        $loans->whereHas('settlement')->whereNotNull('settle_status');
                        // ->where('approvalStatus', '!=', 'completed');
                        break;

                    case 'closed':
                        $loans->whereNotNull('settle_status')
                            ->where('approvalStatus', 'completed');
                        break;

                    case 'rejected':
                        $loans->whereNull('settle_status')
                            ->where('approvalStatus', 'rejected');
                        break;
                }
            }

            if ($request->ledger) {
                $loans->where('name', 'like', '%' . $request->ledger . '%');
            }
            if ($request->type) {
                $loans->where('type', $request->type);
            }
            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('d-m-Y', strtotime($dates[0]));
                $end = date('d-m-Y', strtotime($dates[1]));
                $loans->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
            }
            if (!empty($organization_id)) {
                $loans->where('organization_id', $organization_id);
            }

            return DataTables::of($loans)
                ->addColumn('appli_no', function ($loan) {
                    return $loan->appli_no ? $loan->appli_no : '-';
                })
                ->addColumn('ref_no', function ($loan) {
                    return $loan->ref_no ? $loan->ref_no : '-';
                })
                ->addColumn('proceed_date', function ($loan) {
                    return $loan->proceed_date ? \Carbon\Carbon::parse($loan->proceed_date)->format('d-m-Y') : \Carbon\Carbon::parse($loan->created_at)->format('d-m-Y');
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
                ->addColumn('loan_amount', function ($loan) use ($request) {

                    if ($request->status == 'under settlement') {
                        return $loan->loanAppraisal->term_loan ? Helper::formatIndianNumber($loan->loanAppraisal->term_loan) : Helper::formatIndianNumber($loan->loan_amount);
                    } else {
                        return $loan->loanAppraisal->term_loan ? Helper::formatIndianNumber($loan->loanAppraisal->term_loan) : Helper::formatIndianNumber($loan->loan_amount);
                    }
                })

                ->addColumn('dis_amount', function ($loan) {
                    $totalDisbursement = LoanDisbursement::where('home_loan_id', $loan->id)->where('approvalStatus', 'Disbursed')
                        ->sum(DB::raw('COALESCE(actual_dis,dis_amount)'));
                    return $totalDisbursement ? $totalDisbursement : 0;
                })
                ->addColumn('total_rec', function ($loan) {
                    $maxRecoveryLoan = RecoveryLoan::where('home_loan_id', $loan->id)
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($maxRecoveryLoan) {
                        return round($maxRecoveryLoan->rec_principal_amnt, 2);
                    } else {
                        return 0; // or handle the case where no record is found
                    }
                })

                ->addColumn('rec_loan_amnt', function ($loan) {
                    return Helper::formatIndianNumber(round($loan->recovery_loan_amount, 2)) ?? '-';
                })
                ->addColumn('write_off_amnt', function ($loan) {
                    // $write_off = LoanSettlement::where('home_loan_id', $loan->id)
                    //     ->orderBy('id', 'desc')
                    //     ->first()
                    //     ->settle_wo_amnnt ?? Helper::formatIndianNumber(0);

                    // return round($write_off, 2) ?? '-';
                    return $loan->settle_wo_amnnt;
                })
                ->addColumn('total_int', function ($loan) use ($request) {

                    if ($request->status == 'under settlement') {
                        return $loan->rec_intrst;
                    } else {

                        $totalInterest = RecoveryLoan::where('home_loan_id', $loan->id)
                            ->sum('rec_interest_amnt');
                        return round($totalInterest, 2);
                    }
                })
                ->addColumn('write_off_amnt', function ($loan) {
                    return $loan->settlement->settle_wo_amnnt ?? 0;
                })
                ->addColumn('bal_amount', function ($loan) {
                    $maxRecoveryLoan = RecoveryLoan::where('home_loan_id', $loan->id)
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($maxRecoveryLoan) {
                        return round($maxRecoveryLoan->balance_amount, 2);
                    } else {
                        return 0; // or handle the case where no record is found
                    }
                })
                ->addColumn('ass_recom_amnt', function ($loan) {
                    return $loan->ass_recom_amnt ? $loan->ass_recom_amnt : '-';
                })
                ->addColumn('age', function ($loan) {
                    return $loan->recovery_sentioned ? $loan->recovery_sentioned : '-';
                })
                ->addColumn('status', function ($loan) {
                    // $status = '<span class="badge rounded-pill badge-light-success badgeborder-radius">'.$loan->approvalStatus.'</span>';
                    if (!empty($loan->settle_status)) {
                        $status = '<span class="badge rounded-pill badge-light-success badgeborder-radius">Settlement</span>';
                    } else {
                        $mainBadgeClass = match ($loan->approvalStatus) {
                            'approved' => 'success',
                            'approval_not_required' => 'success',
                            'draft' => 'warning',
                            'submitted' => 'info',
                            'partially_approved' => 'warning',
                            default => 'danger',
                        };

                        if ($mainBadgeClass == 'warning') {
                            $status = "<span class='badge rounded-pill badge-light-warning badgeborder-radius'>" . str_replace('_', ' ', $loan->approvalStatus) . "</span>";
                        } elseif ($mainBadgeClass == 'success') {
                            $status = "<span class='badge rounded-pill badge-light-success badgeborder-radius'>" . str_replace('_', ' ', $loan->approvalStatus) . "</span>";
                        } elseif ($mainBadgeClass == 'danger') {
                            $status = "<span class='badge rounded-pill badge-light-danger badgeborder-radius'>" . str_replace('_', ' ', $loan->approvalStatus) . "</span>";
                        } elseif ($mainBadgeClass == 'info') {
                            $status = "<span class='badge rounded-pill badge-light-info badgeborder-radius'>" . str_replace('_', ' ', $loan->approvalStatus) . "</span>";
                        } else {
                            $status = '-';
                        }
                    }

                    return $status;
                })
                ->editColumn('created_at', function ($data) {
                    $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                    return $formatedDate;
                })
                ->addColumn('action', function ($loan) {
                    $view_route = '';
                    $edit_route = '';
                    $delete_route = '';
                    $url = '';
                    if ($loan->type == 1) {
                        $view_route = 'loan.view_all_detail';
                        $edit_route = 'loan.home-loan-edit';
                        $delete_route = 'loan.home-loan-delete';
                    } elseif ($loan->type == 2) {
                        $view_route = 'loan.view_vehicle_detail';
                        $edit_route = 'loan.edit_vehicle_detail';
                        $delete_route = 'loan.delete_vehicle_detail';
                    } else {
                        $view_route = 'loan.view_term_detail';
                        $edit_route = 'loan.term-loan-edit';
                        $delete_route = 'loan.term-loan-delete';
                    }
                    $view_url = route($view_route, $loan->id);
                    $edit_url = route($edit_route, $loan->id);
                    $delete_url = route($delete_route, $loan->id);
                    $dropdownItems = '';
                    if ($loan->approvalStatus == 'submitted' || $loan->approvalStatus == 'approval_not_required') {
                        $dropdownItems = '
                            <a class="dropdown-item" data-bs-toggle="modal" id="assess" href="#viewassesgive" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->loan_amount . '" data-loan-id="' . $loan->id . '">
                                <i data-feather="file-text" class="me-50"></i>
                                <span>Assessment</span>
                            </a>
                            <a class="dropdown-item" data-bs-toggle="modal" id="docc" href="#viewdocs" data-loan-id="' . $loan->id . '" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->loan_amount . '">
                                <i data-feather="folder" class="me-50"></i>
                                <span>Documents</span>
                            </a>';

                        $url = $view_url;
                    } elseif ($loan->approvalStatus == 'approved' || $loan->approvalStatus == 'partially approved') {

                        $dropdownItems = '
                            <a class="dropdown-item" data-bs-toggle="modal" href="#Disbursement" id="disburs" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->ass_recom_amnt . '" data-loan-id="' . $loan->id . '">
                                <i data-feather="calendar" class="me-50"></i>
                                <span>Disbursal Schedule</span>
                            </a>
                            <a class="dropdown-item" data-bs-toggle="modal" id="r_schedule" href="#Recovery" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->ass_recom_amnt . '" data-loan-id="' . $loan->id . '">
                                <i data-feather="clipboard" class="me-50"></i>
                                <span>Recovery Schedule</span>
                            </a>
                            <a class="dropdown-item" data-bs-toggle="modal" id="docc" href="#viewdocs" data-loan-id="' . $loan->id . '" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->loan_amount . '">
                                <i data-feather="folder" class="me-50"></i>
                                <span>Documents</span>
                            </a>';
                        $url = $view_route;
                    } else {
                        $dropdownItems = '';
                        $url = $view_url;
                    }

                    // $deleteAction = '';
                    // if ($loan->approvalStatus == 'draft') {
                    //     $deleteAction = '<a class="dropdown-item" href="' . $delete_url . '">
                    //     <i data-feather="trash-2" class="me-50"></i>
                    //     <span>Delete</span>
                    //     </a>';
                    //     $url = $edit_url;
                    // }

                    $editAction = '';
                    if ($loan->approvalStatus == 'draft') {
                        $editAction = '<a class="dropdown-item" href="' . $edit_url . '">
                            <i data-feather="edit-3" class="me-50"></i>
                            <span>Edit</span>
                        </a>';
                        $url = $edit_url;
                    }

                    return '<a href="' . $url . '">
                    <i data-feather="eye" class="me-50"></i>
                    </a>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } else {
            $loans = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->where('organization_id', $organization_id)->get();
        }

        // Count for 'under processing' statuses
        $underProcessingCount = HomeLoan::whereNull('settle_status')->whereHas('series')->where('organization_id',$organization_id)

            ->where(function ($query) {
                $query->where('approvalStatus', 'Draft')
                    ->orWhere('approvalStatus', 'draft')
                    ->orWhere('approvalStatus', 'appraisal')
                    ->orWhere('approvalStatus', 'submitted')
                    ->orWhere('approvalStatus', 'assessment')
                    ->orWhere('approvalStatus', 'approval')
                    ->orWhere('approvalStatus', 'legal')
                    ->orWhere('approvalStatus', 'sanctioned')
                    ->orWhere('approvalStatus', 'approved')
                    ->orWhere('approvalStatus', 'partially_approved')
                    ->orWhere('approvalStatus', 'approval_not_required')
                    ->orWhere('approvalStatus', 'legal docs');
            })->count();

        // Count for 'active' statuses
        $activeCount = HomeLoan::whereNull('settle_status')->whereHas('series')
            ->where(function ($query) {
                $query->where('approvalStatus', 'Disbursed')
                    ->orWhere('approvalStatus', 'Disbursed')
                    ->orWhere('approvalStatus', 'recovery');
            })->where('organization_id',$organization_id)->count();

        // Count for 'under settlement' statuses
        $underSettlementCount = HomeLoan::whereHas('settlement')->whereNotNull('settle_status')->whereHas('series')
            // ->where(function ($query) {
            // $query->where('approvalStatus', '!=', 'completed');
            // })
            ->where('organization_id',$organization_id)->count();

        // Count for 'closed' statuses
        $closeCount = HomeLoan::whereNotNull('settle_status')->whereHas('series')
            ->where('approvalStatus', 'completed')
            ->where('organization_id',$organization_id)->count();

        // Count for 'rejected' statuses
        $rejectedCount = HomeLoan::whereNull('settle_status')->whereHas('series')
            ->where('approvalStatus', 'rejected')
            ->where('organization_id',$organization_id)->count();


        return view('loan.index', compact('loans', 'underProcessingCount', 'rejectedCount', 'closeCount', 'activeCount', 'underSettlementCount'));
    }

    public function index_old(Request $request)
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
            // if ($request->has('keyword')) {
            //     $keyword = trim($request->keyword);
            //     $loans->where(function ($query) use ($keyword) {
            //         $query->where('appli_no', 'like', '%' . $keyword . '%')
            //             ->orWhere('ref_no', 'like', '%' . $keyword . '%')
            //             ->orWhere('name', 'like', '%' . $keyword . '%')
            //             ->orWhere('email', 'like', '%' . $keyword . '%')
            //             ->orWhere('mobile', 'like', '%' . $keyword . '%')
            //             ->orWhere('loan_amount', 'like', '%' . $keyword . '%')
            //             ->orWhere('ass_recom_amnt', 'like', '%' . $keyword . '%');

            //         if (strtolower($keyword) === 'home') {
            //             $query->orWhere('type', 1);
            //         } elseif (strtolower($keyword) === 'vehicle') {
            //             $query->orWhere('type', 2);
            //         } elseif (strtolower($keyword) === 'term') {
            //             $query->orWhere('type', 3);
            //         }
            //     });
            // }

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

            return DataTables::of($loans)
                ->addColumn('appli_no', function ($loan) {
                    return $loan->appli_no ? $loan->appli_no : '-';
                })
                ->addColumn('ref_no', function ($loan) {
                    return $loan->ref_no ? $loan->ref_no : '-';
                })
                ->addColumn('proceed_date', function ($loan) {
                    return $loan->proceed_date ? \Carbon\Carbon::parse($loan->proceed_date)->format('d-m-Y') : '-';
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
                    return $loan->loan_amount ? $loan->loan_amount : '-';
                })
                ->addColumn('ass_recom_amnt', function ($loan) {
                    return $loan->ass_recom_amnt ? $loan->ass_recom_amnt : '-';
                })
                ->addColumn('age', function ($loan) {
                    return $loan->recovery_sentioned ? $loan->recovery_sentioned : '-';
                })
                ->addColumn('status', function ($loan) {
                    if (!empty($loan->settle_status)) {
                        $status = '<span class="badge rounded-pill badge-light-success badgeborder-radius">Settlement</span>';
                    } else {
                        $mainBadgeClass = match ($loan->approvalStatus) {
                            'approved' => 'success',
                            'approval_not_required' => 'success',
                            'draft' => 'warning',
                            'submitted' => 'info',
                            'partially_approved' => 'warning',
                            default => 'danger',
                        };

                        if ($mainBadgeClass == 'warning') {
                            $status = "<span class='badge rounded-pill badge-light-warning badgeborder-radius'>" . str_replace('_', ' ', $loan->approvalStatus) . "</span>";
                        } elseif ($mainBadgeClass == 'success') {
                            $status = "<span class='badge rounded-pill badge-light-success badgeborder-radius'>" . str_replace('_', ' ', $loan->approvalStatus) . "</span>";
                        } elseif ($mainBadgeClass == 'danger') {
                            $status = "<span class='badge rounded-pill badge-light-danger badgeborder-radius'>" . str_replace('_', ' ', $loan->approvalStatus) . "</span>";
                        } elseif ($mainBadgeClass == 'info') {
                            $status = "<span class='badge rounded-pill badge-light-info badgeborder-radius'>" . str_replace('_', ' ', $loan->approvalStatus) . "</span>";
                        } else {
                            $status = '-';
                        }
                    }

                    return $status;
                })
                ->editColumn('created_at', function ($data) {
                    $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                    return $formatedDate;
                })
                ->addColumn('action', function ($loan) {
                    $view_route = '';
                    $edit_route = '';
                    $delete_route = '';
                    if ($loan->type == 1) {
                        $view_route = 'loan.view_all_detail';
                        $edit_route = 'loan.home-loan-edit';
                        $delete_route = 'loan.home-loan-delete';
                    } elseif ($loan->type == 2) {
                        $view_route = 'loan.view_vehicle_detail';
                        $edit_route = 'loan.edit_vehicle_detail';
                        $delete_route = 'loan.delete_vehicle_detail';
                    } else {
                        $view_route = 'loan.view_term_detail';
                        $edit_route = 'loan.term-loan-edit';
                        $delete_route = 'loan.term-loan-delete';
                    }
                    $view_url = route($view_route, $loan->id);
                    $edit_url = route($edit_route, $loan->id);
                    $delete_url = route($delete_route, $loan->id);
                    $dropdownItems = '';
                    if ($loan->approvalStatus == 'submitted' || $loan->approvalStatus == 'approval_not_required') {
                        $dropdownItems = '
                            <a class="dropdown-item" data-bs-toggle="modal" id="assess" href="#viewassesgive" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->loan_amount . '" data-loan-id="' . $loan->id . '">
                                <i data-feather="file-text" class="me-50"></i>
                                <span>Assessment</span>
                            </a>
                            <a class="dropdown-item" data-bs-toggle="modal" id="docc" href="#viewdocs" data-loan-id="' . $loan->id . '" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->loan_amount . '">
                                <i data-feather="folder" class="me-50"></i>
                                <span>Documents</span>
                            </a>';
                    } elseif ($loan->approvalStatus == 'approved' || $loan->approvalStatus == 'partially_approved') {
                        $dropdownItems = '
                            <a class="dropdown-item" data-bs-toggle="modal" href="#Disbursement" id="disburs" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->ass_recom_amnt . '" data-loan-id="' . $loan->id . '">
                                <i data-feather="calendar" class="me-50"></i>
                                <span>Disbursal Schedule</span>
                            </a>
                            <a class="dropdown-item" data-bs-toggle="modal" id="r_schedule" href="#Recovery" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->ass_recom_amnt . '" data-loan-id="' . $loan->id . '">
                                <i data-feather="clipboard" class="me-50"></i>
                                <span>Recovery Schedule</span>
                            </a>
                            <a class="dropdown-item" data-bs-toggle="modal" id="docc" href="#viewdocs" data-loan-id="' . $loan->id . '" data-loan-name="' . $loan->name . '" data-loan-created-at="' . $loan->created_at . '" data-loan-amnt="' . $loan->loan_amount . '">
                                <i data-feather="folder" class="me-50"></i>
                                <span>Documents</span>
                            </a>';
                    } else {
                        $dropdownItems = '';
                    }

                    $deleteAction = '';
                    if ($loan->approvalStatus == 'submitted' || $loan->approvalStatus == 'draft') {
                        $deleteAction = '<a class="dropdown-item" href="' . $delete_url . '">
                            <i data-feather="trash-2" class="me-50"></i>
                            <span>Delete</span>
                        </a>';
                    }

                    $editAction = '';
                    if ($loan->approvalStatus == 'draft' || $loan->approvalStatus == 'submitted') {
                        $editAction = '<a class="dropdown-item" href="' . $edit_url . '">
                            <i data-feather="edit-3" class="me-50"></i>
                            <span>Edit</span>
                        </a>';
                    }

                    return '
                    <div class="dropdown">
                    <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                        <i data-feather="more-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="' . $view_url . '">
                            <i data-feather="check-circle" class="me-50"></i>
                            <span>View Detail</span>
                        </a>'
                        . $deleteAction . $editAction . $dropdownItems .
                        '</div>
                </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } else {
            $loans = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->where('organization_id', $organization_id)->get();
        }

        return view('loan.index', compact('loans'));
    }

    public function viewAllDetail($id)
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

        $overview = ErpLoanAppraisal::with('loan', 'disbursal', 'recovery', 'dpr')->where('loan_id', $id)->first();
        $loan_disbursement = LoanDisbursement::with('homeLoan')->where('home_loan_id', $id)->get();
        $recovery_loan = RecoveryLoan::with('homeLoan')->where('home_loan_id', $id)->get();

        $document_listing = Helper::documentListing($id);

        $homeLoan->loanable_type = strtolower(class_basename($homeLoan->loanable_type));
$buttons = Helper::actionButtonDisplayForLoan($homeLoan->series, $homeLoan->approvalStatus,$homeLoan->id,$homeLoan->loan_amount,$homeLoan->approval_level,$homeLoan->loanable_id,$homeLoan->loanable_type);        $logs = Helper::getLogs($id);
        return view('loan.home_loan_add', compact('homeLoan', 'occupation', 'series', 'book_type', 'logsGroupedByStatus', 'view_detail', 'interest_rate', 'page', 'overview', 'loan_disbursement', 'recovery_loan', 'document_listing', 'buttons', 'logs'));
    }


    public function home_loan()
    {
        
        // $user = Helper::getAuthenticatedUser();
        // $home_loan = HomeLoan::orderBy('id', 'desc')->get();
        // $parentURL = request() -> segments()[1];
        $parentURL = "loan_home-loan";
        
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
        $occupation = DB::table('erp_loan_occupations')->get();
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        $firstService = $servicesBooks['services'][0];
        return view('loan.home_loan_add', compact('occupation', 'series', 'book_type'));
    }

    public function vehicle_loan()
    {

        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        // $parentURL = request() -> segments()[1];
        $parentURL = "loan_vehicle-loan";
        
         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
        
        $vehicle_loan=0;
        $vehicleLoan='';
        
        return view('loan.vehicle_loan', compact('series', 'book_type','vehicle_loan'));
    }

    public function term_loan()
    {
        $user = Helper::getAuthenticatedUser();
        $series = Book::where('organization_id', $user->organization_id)->select('id', 'book_name')->get();
        // $parentURL = request() -> segments()[1];
        $parentURL = "loan_term-loan";
        
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
           return redirect() -> route('/');
       }
       $firstService = $servicesBooks['services'][0];
       $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
        return view('loan.term_loan', compact('series', 'book_type'));
    }


    public function recovery(Request $request)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        if ($request->ajax()) {
            $loans = RecoveryLoan::with('homeLoan')->select('erp_recovery_loans.*')->orderBy('erp_recovery_loans.id', 'desc');
            if ($request->ledger || $request->type || $request->keyword) {
                $loans->leftJoin('erp_home_loans', 'erp_home_loans.id', '=', 'erp_recovery_loans.home_loan_id');
            }

            if ($request->has('keyword')) {
                $keyword = trim($request->keyword);
                if ($request->ledger || $request->type || $request->keyword) {
                    $loans->where(function ($query) use ($keyword) {
                        $query->where('erp_home_loans.appli_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_recovery_loans.document_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.name', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.mobile', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.loan_amount', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.ass_recom_amnt', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_recovery_loans.recovery_amnnt', 'like', '%' . $keyword . '%');

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
                $loans->where('erp_recovery_loans.approvalStatus', $request->status);
            }
            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($dates[0]));
                $end = date('Y-m-d', strtotime($dates[1]));
                $loans->whereDate('erp_recovery_loans.created_at', '>=', $start)->whereDate('erp_recovery_loans.created_at', '<=', $end);
            }
            if (!empty($organization_id)) {
                $loans->where('erp_recovery_loans.organization_id', $organization_id);
            }
            $loans = $loans->get();

            return DataTables::of($loans)
                ->addColumn('appli_no', function ($loan) {
                    return $loan->homeLoan->appli_no ? $loan->homeLoan->appli_no : '-';
                })
                ->addColumn('document_no', function ($loan) {
                    return $loan->document_no ? $loan->document_no : '-';
                })
                ->addColumn('payment_date', function ($loan) {
                    return $loan->payment_date ? \Carbon\Carbon::parse($loan->payment_date)->format('d-m-Y') : '-';
                })
                ->addColumn('name', function ($loan) {
                    return $loan->homeLoan->name ? $loan->homeLoan->name : '-';
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
                    return $loan->homeLoan->loan_amount ? $loan->homeLoan->loan_amount : '-';
                })
                ->addColumn('ass_recom_amnt', function ($loan) {
                    return $loan->homeLoan->recoveryScheduleLoan ? $loan->homeLoan->recoveryScheduleLoan->sum('total') : '-';
                })
                ->addColumn('recovery_amnnt', function ($loan) {
                    return $loan->recovery_amnnt ? $loan->recovery_amnnt : '-';
                })
                ->addColumn('rec_principal_amnt', function ($loan) {
                    // $bal_princ_amnt = 0;
                    // $rec_intrst = 0;
                    // if (!empty($loan->homeLoan->bal_princ_amnt)) {
                    //     $bal_princ_amnt = $loan->homeLoan->bal_princ_amnt;
                    // }
                    // if (!empty($loan->homeLoan->rec_intrst)) {
                    //     $rec_intrst = $loan->homeLoan->rec_intrst;
                    // }
                    // return $bal_princ_amnt + $rec_intrst;
                    return $loan->homeLoan->recovery_total;
                })
                ->addColumn('status', function ($loan) {
                    if ($loan->approvalStatus == 'submitted') {
                        $status = '<span class="badge rounded-pill badge-light-info badgeborder-radius">Submitted</span>';
                    } elseif ($loan->approvalStatus == 'approved') {
                        $status = '<span class="badge rounded-pill badge-light-success badgeborder-radius">Approved</span>';
                    } elseif ($loan->approvalStatus == 'approval_not_required') {
                        $status = '<span class="badge rounded-pill badge-light-success badgeborder-radius">Approval Not Required</span>';
                    } elseif ($loan->approvalStatus == 'rejected') {
                        $status = '<span class="badge rounded-pill badge-light-danger badgeborder-radius">Rejected</span>';
                    } else {
                        $status = '-';
                    }
                    return $status;
                })
                ->editColumn('created_at', function ($data) {
                    $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                    return $formatedDate;
                })
                ->addColumn('action', function ($loan) {
                    $view_route = '';
                    if ($loan->homeLoan->type == 1) {
                        $view_route = 'loan.view_all_detail';
                    } elseif ($loan->homeLoan->type == 2) {
                        $view_route = 'loan.view_vehicle_detail';
                    } else {
                        $view_route = 'loan.view_term_detail';
                    }
                    $view_url = route($view_route, $loan->homeLoan->id);
                    return '
                    <div class="dropdown">
                    <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                        <i data-feather="more-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="' . $view_url . '">
                            <i data-feather="check-circle" class="me-50"></i>
                            <span>View Detail</span>
                        </a>
                    </div>
                    <input type="hidden" data-dis="' . $loan->id . '" id="getID" value="' . $loan->id . '">
                    <input type="hidden" id="getLoanAmount" value="' . $loan->homeLoan->loan_amount . '">
                    <input type="hidden" id="getSTATUS" value="' . $loan->approvalStatus . '">
                    <input type="hidden" id="getNameRecord" value="' . $loan->homeLoan->name . '">
                    <input type="hidden" id="getDateRecord" value="' . $loan->homeLoan->created_at->format('Y-m-d') . '">
                </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } else {
            $loans = RecoveryLoan::with('homeLoan')->where('organization_id', $organization_id)->orderBy('id', 'desc');
        }
        $customer_names = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->get();

        return view('loan.recovery', compact('loans', 'customer_names'));
    }

    public function settlement(Request $request)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        if ($request->ajax()) {

            $loans = LoanSettlement::with('homeLoan')->select('erp_loan_settlements.*')->orderBy('erp_loan_settlements.id', 'desc');
            if ($request->ledger || $request->type || $request->keyword) {
                $loans->leftJoin('erp_home_loans', 'erp_home_loans.id', '=', 'erp_loan_settlements.home_loan_id');
            }

            if ($request->has('keyword')) {
                $keyword = trim($request->keyword);
                if ($request->ledger || $request->type || $request->keyword) {
                    $loans->where(function ($query) use ($keyword) {
                        $query->where('erp_home_loans.appli_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_loan_settlements.settle_document_no', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.name', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.mobile', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_home_loans.loan_amount', 'like', '%' . $keyword . '%')
                            ->orWhere('erp_loan_settlements.settle_amnnt', 'like', '%' . $keyword . '%');

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
                $loans->where('erp_loan_settlements.status', $request->status);
            }
            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($dates[0]));
                $end = date('Y-m-d', strtotime($dates[1]));
                $loans->whereDate('erp_loan_settlements.created_at', '>=', $start)->whereDate('erp_loan_settlements.created_at', '<=', $end);
            }
            if (!empty($organization_id)) {
                $loans->where('erp_loan_settlements.organization_id', $organization_id);
            }
            $loans = $loans->get();

            return DataTables::of($loans)
                ->addColumn('appli_no', function ($loan) {
                    return $loan->homeLoan->appli_no ? $loan->homeLoan->appli_no : '-';
                })
                ->addColumn('settle_document_no', function ($loan) {
                    return $loan->settle_document_no ? $loan->settle_document_no : '-';
                })
                ->addColumn('created_at', function ($loan) {
                    return $loan->created_at ? $loan->created_at : '-';
                })
                ->addColumn('name', function ($loan) {
                    return $loan->homeLoan->name ? $loan->homeLoan->name : '-';
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
                    return $loan->homeLoan->loan_amount ? $loan->homeLoan->loan_amount : '-';
                })
                ->addColumn('settle_amnnt', function ($loan) {
                    return $loan->settle_amnnt ? $loan->settle_amnnt : '-';
                })
                ->addColumn('status', function ($loan) {
                    if ($loan->status == 0) {
                        $status = '<span class="badge rounded-pill badge-light-info badgeborder-radius">Submitted</span>';
                    } elseif ($loan->status == 1) {
                        $status = '<span class="badge rounded-pill badge-light-success badgeborder-radius">Approved</span>';
                    } elseif ($loan->status == 2) {
                        $status = '<span class="badge rounded-pill badge-light-danger badgeborder-radius">Rejected</span>';
                    } else {
                        $status = '-';
                    }
                    return $status;
                })
                ->editColumn('created_at', function ($data) {
                    $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                    return $formatedDate;
                })
                ->addColumn('action', function ($loan) {
                    $view_route = '';
                    if ($loan->homeLoan->type == 1) {
                        $view_route = 'loan.view_all_detail';
                    } elseif ($loan->homeLoan->type == 2) {
                        $view_route = 'loan.view_vehicle_detail';
                    } else {
                        $view_route = 'loan.view_term_detail';
                    }
                    $view_url = route($view_route, $loan->homeLoan->id);
                    return '
                    <div class="dropdown">
                    <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                        <i data-feather="more-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="' . $view_url . '">
                            <i data-feather="check-circle" class="me-50"></i>
                            <span>View Detail</span>
                        </a>
                    </div>
                    <input type="hidden" data-dis="' . $loan->id . '" id="getID" value="' . $loan->id . '">
                    <input type="hidden" id="getLoanAmount" value="' . $loan->homeLoan->loan_amount . '">
                    <input type="hidden" id="getSTATUS" value="' . $loan->status . '">
                    <input type="hidden" id="getNameRecord" value="' . $loan->homeLoan->name . '">
                    <input type="hidden" id="getDateRecord" value="' . $loan->homeLoan->created_at->format('Y-m-d') . '">
                </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } else {
            $loans = LoanSettlement::with('homeLoan')->where('organization_id', $organization_id)->orderBy('id', 'desc');
        }
        $customer_names = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->get();

        return view('loan.settlement', compact('loans', 'customer_names'));
    }

    public function interest_rate()
    {
        $interest_rate = InterestRate::orderBy('id', 'desc')->get();
        return view('loan.interest_rate', compact('interest_rate'));
    }

    public function getCities(Request $request)
    {
        $search = $request->input('q');
        $cities = DB::table('cities')->where('name', 'LIKE', "%$search%")->limit(20)->get();

        return response()->json($cities->map(function ($city) {
            return ['id' => $city->id, 'name' => $city->name];
        }));
    }

    public function getCityByID(Request $request)
    {
        $cityId = $request->input('id');
        $city = DB::table('cities')->find($cityId);

        return response()->json([
            'id' => $city->id,
            'name' => $city->name
        ]);
    }


    public function getStates(Request $request)
    {
        $search = $request->input('q');
        $states = DB::table('states')->where('name', 'LIKE', "%$search%")->limit(20)->get();

        return response()->json($states->map(function ($state) {
            return ['id' => $state->id, 'name' => $state->name];
        }));
    }

    public function getStateByID(Request $request)
    {
        $stateId = $request->input('id');
        $state = DB::table('states')->find($stateId);

        return response()->json([
            'id' => $state->id,
            'name' => $state->name
        ]);
    }

    public function ApprReject(Request $request)
    {
        $image_customer = null;
        if ($request->has('appr_rej_doc')) {
            $path = $request->file('appr_rej_doc')->store('loan_documents', 'public');
            $image_customer = $path;
        } elseif ($request->has('stored_appr_rej_doc')) {
            $image_customer = $request->stored_appr_rej_doc;
        } else {
            $image_customer = null;
        }
        $series = HomeLoan::find($request->appr_rej_loan_id)->series;
        $status = $request->appr_rej_status == ConstantHelper::SUBMITTED ? Helper::checkApprovalRequired($series) : $request->appr_rej_status;

        //dd($status);
        HomeLoan::updateOrCreate([
            'id' => $request->appr_rej_loan_id
        ], [
            'appr_rej_recom_amnt' => $request->appr_rej_recommended_amnt ?? null,
            'appr_rej_recom_remark' => $request->appr_rej_remarks ?? null,
            'appr_rej_doc' => $image_customer,
            'appr_rej_behalf_of' => $request->appr_rej_behalf_of ? json_encode($request->appr_rej_behalf_of) : null,
            'status' => $status
        ]);

        $homeLoan = HomeLoan::find($request->appr_rej_loan_id);

        if ($homeLoan->status == 2) {
            // $action_type = 'approve';
            // $email_data = [
            //     'username' => (string) $homeLoan->name,
            //     'title' => "Loan Approval",
            //     'message' => "Congratulations, {$homeLoan->name}! Your loan application (Application ID: {$homeLoan->appli_no}) has been approved for an amount of {$homeLoan->appr_rej_recom_amnt} at an interest rate of {$homeLoan->recovery_sentioned}% per annum. ",
            // ];

            // try {
            //     Mail::to($homeLoan->email)->send(new LoanUserMessageMail($email_data));
            //     // Log success
            //     Log::info("Email sent successfully to {$homeLoan->email} for application {$homeLoan->appli_no}");
            // } catch (\Exception $e) {
            //     // Log the full error
            //     return redirect()->back()->with("error", "Failed to send email. Please try again later.");
            // }
        } elseif ($homeLoan->status == 3) {
            // $action_type = 'reject';
            // $email_data = [
            //     'username' => (string) $homeLoan->name,
            //     'title' => "Loan Rejection",
            //     'message' => "Dear {$homeLoan->name}, unfortunately, we are unable to approve your loan application (Application ID: {$homeLoan->appli_no}) at this moment. We appreciate your interest in MIDC",
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

        $type = $homeLoan ? $homeLoan->type : null;
        $loan_type = '';
        if ($type == 1) {
            $loan_type = 'home';
        } elseif ($type == 2) {
            $loan_type = 'vehicle';
        } elseif ($type == 3) {
            $loan_type = 'term';
        }

        $action_type = '';
        if ($request->appr_rej_status == 2) {
            $action_type = 'approve';
        } elseif ($request->appr_rej_status == 3) {
            $action_type = 'reject';
        }
        $logExists = LoanApplicationLog::checkActivityLog($request->appr_rej_loan_id, $loan_type, self::$user_id, $action_type);
        if (!$logExists) {
            $loanApplicationLog = LoanApplicationLog::create([
                'loan_application_id' => $request->appr_rej_loan_id,
                'loan_type' => $loan_type,
                'action_type' => $action_type,
                'user_id' => self::$user_id,
                'remarks' => $request->appr_rej_remarks
            ]);
        }

        if ($request->appr_rej_status == 2) {
            return redirect("loan/my-application")->with('success', 'Approved Successfully!');
        } else {
            return redirect("loan/my-application")->with('success', 'Rejected Successfully!');
        }
    }

    public function loanAssessment(Request $request)
    {
        $image_customer = null;
        if ($request->has('ass_doc')) {
            $path = $request->file('ass_doc')->store('loan_documents', 'public');
            $image_customer = $path;
        } elseif ($request->has('stored_ass_doc')) {
            $image_customer = $request->stored_ass_doc;
        } else {
            $image_customer = null;
        }
        $interest_rate = InterestRateScore::where('cibil_score_min', '<=', $request->ass_cibil)
            ->where('cibil_score_max', '>=', $request->ass_cibil)
            ->select('interest_rate')
            ->first();
        $interest_rate = $interest_rate->interest_rate;

        $bal_intrst_amnt = ($request->ass_recom_amnt * (int) $interest_rate) / 100;
        $req_status = "submitted";
        $status = $req_status == ConstantHelper::SUBMITTED ? Helper::checkApprovalRequired($request->series) : $req_status;

        $data = HomeLoan::updateOrCreate([
            'id' => $request->loan_id
        ], [
            'ass_recom_amnt' => $request->ass_recom_amnt ?? null,
            'ass_cibil' => $request->ass_cibil ?? null,
            'ass_remarks' => $request->ass_remarks ?? null,
            'ass_doc' => $image_customer,
            'bal_princ_amnt' => $request->ass_recom_amnt ?? null,
            'bal_intrst_amnt' => $bal_intrst_amnt,
            'approvalStatus' => $status
        ]);

        $homeLoan = HomeLoan::find($request->loan_id);
        $type = $homeLoan ? $homeLoan->type : null;
        $loan_type = '';
        if ($type == 1) {
            $loan_type = 'home';
        } elseif ($type == 2) {
            $loan_type = 'vehicle';
        } elseif ($type == 3) {
            $loan_type = 'term';
        }

        $action_type = '';
        if ($request->appr_rej_status == 2) {
            $action_type = 'approve';
        } elseif ($request->appr_rej_status == 3) {
            $action_type = 'reject';
        }
        $logExists = LoanApplicationLog::checkActivityLog($request->loan_id, $loan_type, self::$user_id, 'assessment');
        if (!$logExists) {
            $loanApplicationLog = LoanApplicationLog::create([
                'loan_application_id' => $request->loan_id,
                'loan_type' => $loan_type,
                'action_type' => 'assessment',
                'user_id' => self::$user_id,
                'remarks' => $request->ass_remarks
            ]);
        }

        return redirect("loan/my-application")->with('success', 'Assessment added successfully!');
    }

    public function getAssessment(Request $request)
    {
        $assess = HomeLoan::find($request->id);
        return response()->json([
            'assess' => [
                'ass_recom_amnt' => $assess->ass_recom_amnt,
                'ass_cibil' => $assess->ass_cibil,
                'ass_remarks' => $assess->ass_remarks,
                'ass_doc' => $assess->ass_doc
            ]
        ]);
    }

    public function loanDisbursemnt(Request $request)
    {
        $req_status = "submitted";
        $status = $req_status == ConstantHelper::SUBMITTED ? Helper::checkApprovalRequired($request->series) : $req_status;

        $data = HomeLoan::updateOrCreate([
            'id' => $request->loan_idd
        ], [
            'disbursal_amnt' => $request->disbursement_amnt ?? null,
            'disbursal_percent' => $request->disbursal_percent ?? null,
            'disbursal_date_type' => $request->disbursal_date_type ?? null,
            'disbursal_due_date' => $request->disbursal_due_date ?? null,
            'disbursal_val' => $request->dis_valuE ?? null,
            'approvalStatus' => $status,
        ]);

        $disbursal = $request->input('Disbursal', []);
        if (count($disbursal) > 0) {
            DisbursalLoan::where('home_loan_id', $request->loan_idd)->delete();
            foreach ($disbursal['dis_amount'] as $index => $dis_amount) {
                DisbursalLoan::create([
                    'home_loan_id' => $request->loan_idd,
                    'milestone' => $disbursal['milestone'][$index] ?? null,
                    'dis_amount' => $disbursal['dis_amount'][$index] ?? null,
                    'dis_date' => $disbursal['dis_date'][$index] ?? null
                ]);
            }
        }

        $homeLoan = HomeLoan::find($request->loan_idd);
        $type = $homeLoan ? $homeLoan->type : null;
        $loan_type = '';
        if ($type == 1) {
            $loan_type = 'home';
        } elseif ($type == 2) {
            $loan_type = 'vehicle';
        } elseif ($type == 3) {
            $loan_type = 'term';
        }

        $logExists = LoanApplicationLog::checkActivityLog($request->loan_id, $loan_type, self::$user_id, 'disbursal');
        if (!$logExists) {
            $loanApplicationLog = LoanApplicationLog::create([
                'loan_application_id' => $request->loan_idd,
                'loan_type' => $loan_type,
                'action_type' => 'disbursal',
                'user_id' => self::$user_id,
                'remarks' => null
            ]);
        }

        return redirect("loan/my-application")->with('success', 'Disbursement added successfully!');
    }

    public function getDisbursemnt(Request $request)
    {
        $disburs = HomeLoan::with('disbursalLoan')->find($request->id);
        $html = '';
        $intrest_rate = '';
        if ($disburs->ass_cibil !== null) {
            $intrest_rate = InterestRateScore::where('cibil_score_min', '<=', $disburs->ass_cibil)
                ->where('cibil_score_max', '>=', $disburs->ass_cibil)
                ->select('interest_rate')
                ->first()->interest_rate;
        }
        $today = date('Y-m-d');
        $dis_count = $disburs->disbursalLoan->count();
        $total_disbursal = 0;
        if ($disburs->disbursalLoan && $disburs->disbursalLoan->count() > 0) {
            foreach ($disburs->disbursalLoan as $key => $val) {
                $total_disbursal += $val->dis_amount;
                // if($key == 0){
                //     $html .= '<tr>
                //         <td id="row-number-dis">1</td>
                //         <td><input type="text" name="Disbursal[milestone][]" id="dis_mile" class="form-control mw-100 dis_mile"></td>
                //         <td><input type="number" name="Disbursal[dis_amount][]" class="form-control mw-100 dis_amnt" id="dis_amnt"></td>
                //         <td><input type="date" name="Disbursal[dis_date][]" class="form-control mw-100 dis_date" id="dis_date" min="' . $today . '"></td>
                //     </tr>';
                // }
                $html .= '<tr>
                    <td>' . ($key + 1) . '</td>
                    <td><input type="text" name="Disbursal[milestone][]" id="dis_mile" value="' . htmlspecialchars($val->milestone ?? '') . '" class="form-control mw-100 dis_mile" required readonly></td>
                    <td><input type="number" name="Disbursal[dis_amount][]" id="dis_amnt" value="' . htmlspecialchars($val->dis_amount ?? '') . '" class="form-control mw-100 dis_amnt" required readonly></td>
                    <td><input type="date" name="Disbursal[dis_date][]" id="dis_date" value="' . htmlspecialchars($val->dis_date ?? '') . '" class="form-control mw-100 dis_date" required readonly min="' . $today . '"></td>
                </tr>';
            }
            $disbursal_show = 0;
        } else {
            $html = "<tr>
                <td id='row-number-dis'>1</td>
                <td><input type='text' name='Disbursal[milestone][]' id='dis_mile' class='form-control mw-100 dis_mile'></td>
                <td><input type='number' name='Disbursal[dis_amount][]' id='dis_amnt' value='{$disburs->ass_recom_amnt}' class='form-control mw-100 dis_amnt'></td>
                <td><input type='date' name='Disbursal[dis_date][]' id='dis_date' class='form-control mw-100 dis_date' min='{$today}'></td>
                <td><a href='#' class='text-primary add-row'><i data-feather='plus-square'></i></a></td>
            </tr>";
            $disbursal_show = 1;
        }

        return response()->json(['disburs' => $html, 'loan_amount' => $disburs, 'dis_count' => $dis_count, 'intrest_rate' => $intrest_rate, 'total_disbursal' => $total_disbursal, 'disbursal_show' => $disbursal_show]);
    }

    public function getDoc(Request $request)
    {
        $data = HomeLoan::find($request->id);
        if ($data->type == 1) {
            $documents = HomeLoan::with('documents')->find($request->id);

            $adhar_card_doc = json_decode($documents->documents->adhar_card, true);
            $gir_no_doc = json_decode($documents->documents->gir_no, true);
            $plot_doc = json_decode($documents->documents->plot_doc, true);
            $land_doc = json_decode($documents->documents->land_doc, true);
            $income_proof_doc = json_decode($documents->documents->income_proof, true);

            if (is_array($adhar_card_doc) && count($adhar_card_doc) > 0) {
                $adhar_card = '';
                foreach ($adhar_card_doc as $doc) {
                    $adhar_card .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $adhar_card = 'No Document';
            }

            if (is_array($gir_no_doc) && count($gir_no_doc) > 0) {
                $gir_no = '';
                foreach ($gir_no_doc as $doc) {
                    $gir_no .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $gir_no = 'No Document';
            }

            if (is_array($plot_doc) && count($plot_doc) > 0) {
                $plot = '';
                foreach ($plot_doc as $doc) {
                    $plot .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $plot = 'No Document';
            }

            if (is_array($land_doc) && count($land_doc) > 0) {
                $land = '';
                foreach ($land_doc as $doc) {
                    $land .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $land = 'No Document';
            }

            if (is_array($income_proof_doc) && count($income_proof_doc) > 0) {
                $income_proof = '';
                foreach ($income_proof_doc as $doc) {
                    $income_proof .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $income_proof = 'No Document';
            }

            $ass = $documents->ass_doc ? '<a href="' . asset('storage/' . $documents->ass_doc) . '" target="_blank" download><i data-feather="download"></i></a>' : 'No Document';
            $appr_rej = $documents->appr_rej_doc ? '<a href="' . asset('storage/' . $documents->appr_rej_doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>' : 'No Document';


            $statusText = $documents->status == 2 ? 'Approve' : 'Reject';
            $html = '<tr><td>1</td><td>Aadhar Card</td><td>' . $adhar_card . '</td></tr>
                    <tr><td>2</td><td>PAN/GIR No.</td><td>' . $gir_no . '</td></tr>
                    <tr><td>3</td><td>Plot Document</td><td>' . $plot . '</td></tr>
                    <tr><td>4</td><td>Land Document</td><td>' . $land . '</td></tr>
                    <tr><td>5</td><td>Income Proof</td><td>' . $income_proof . '</td></tr>
                    <tr><td>6</td><td>' . $statusText . '</td><td>' . $appr_rej . '</td></tr>
                    <tr><td>7</td><td>Assessment</td><td>' . $ass . '</td></tr>';

            return response()->json(['doc' => $html]);
        } elseif ($data->type == 2) {
            $documents = HomeLoan::with('vehicleDocuments')->find($request->id);

            $adhar_card_doc = json_decode($documents->vehicleDocuments->adhar_card, true);
            $pan_gir_no_doc = json_decode($documents->vehicleDocuments->pan_gir_no, true);
            $vehicle_doc = json_decode($documents->vehicleDocuments->vehicle_doc, true);
            $security_doc = json_decode($documents->vehicleDocuments->security_doc, true);
            $partnership_doc = json_decode($documents->vehicleDocuments->partnership_doc, true);
            $affidavit_doc = json_decode($documents->vehicleDocuments->affidavit_doc, true);
            $scan_doc = json_decode($documents->vehicleDocuments->scan_doc, true);

            if (is_array($adhar_card_doc) && count($adhar_card_doc) > 0) {
                $adhar_card = '';
                foreach ($adhar_card_doc as $doc) {
                    $adhar_card .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $adhar_card = 'No Document';
            }

            if (is_array($pan_gir_no_doc) && count($pan_gir_no_doc) > 0) {
                $pan_gir_no = '';
                foreach ($pan_gir_no_doc as $doc) {
                    $pan_gir_no .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $pan_gir_no = 'No Document';
            }

            if (is_array($vehicle_doc) && count($vehicle_doc) > 0) {
                $vehicle = '';
                foreach ($vehicle_doc as $doc) {
                    $vehicle .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $vehicle = 'No Document';
            }

            if (is_array($security_doc) && count($security_doc) > 0) {
                $security = '';
                foreach ($security_doc as $doc) {
                    $security .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $security = 'No Document';
            }

            if (is_array($partnership_doc) && count($partnership_doc) > 0) {
                $partnership = '';
                foreach ($partnership_doc as $doc) {
                    $partnership .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $partnership = 'No Document';
            }

            if (is_array($affidavit_doc) && count($affidavit_doc) > 0) {
                $affidavit = '';
                foreach ($affidavit_doc as $doc) {
                    $affidavit .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $affidavit = 'No Document';
            }

            if (is_array($scan_doc) && count($scan_doc) > 0) {
                $scan = '';
                foreach ($scan_doc as $doc) {
                    $scan .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $scan = 'No Document';
            }

            $ass_doc = $documents->ass_doc ? '<a href="' . asset('storage/' . $documents->ass_doc) . '" target="_blank" download><i data-feather="download"></i></a>' : 'No Document';
            $appr_rej_doc = $documents->appr_rej_doc ? '<a href="' . asset('storage/' . $documents->appr_rej_doc) . '" target="_blank" download><i data-feather="download"></i></a>' : 'No Document';

            $statusText = $documents->status == 2 ? 'Approve' : 'Reject';
            $html = '<tr><td>1</td><td>Aadhar Card</td><td>' . $adhar_card . '</td></tr>
                    <tr><td>2</td><td>PAN/GIR No.</td><td>' . $pan_gir_no . '</td></tr>
                    <tr><td>3</td><td>Vehicle Document</td><td>' . $vehicle . '</td></tr>
                    <tr><td>4</td><td>Security Document</td><td>' . $security . '</td></tr>
                    <tr><td>5</td><td>Partnership Affidavit</td><td>' . $partnership . '</td></tr>
                    <tr><td>6</td><td>Proprietorship Affidavit</td><td>' . $affidavit . '</td></tr>
                    <tr><td>7</td><td>Scan form Application</td><td>' . $scan . '</td></tr>
                    <tr><td>8</td><td>' . $statusText . '</td><td>' . $appr_rej_doc . '</td></tr>
                    <tr><td>9</td><td>Assessment</td><td>' . $ass_doc . '</td></tr>';

            return response()->json(['doc' => $html]);
        } elseif ($data->type == 3) {
            $documents = HomeLoan::with('termLoanDocument', 'termLoanPromoter', 'termLoanNetWorth.termLoanNetWorthExperience')->find($request->id);

            $adhar_card_doc = json_decode($documents->termLoanDocument->adhar_card, true);
            $gir_no_doc = json_decode($documents->termLoanDocument->gir_no, true);
            $asset_proof_doc = json_decode($documents->termLoanDocument->asset_proof, true);
            $application_doc = json_decode($documents->termLoanDocument->application, true);

            if (is_array($adhar_card_doc) && count($adhar_card_doc) > 0) {
                $adhar_card = '';
                foreach ($adhar_card_doc as $doc) {
                    $adhar_card .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $adhar_card = 'No Document';
            }

            if (is_array($gir_no_doc) && count($gir_no_doc) > 0) {
                $gir_no = '';
                foreach ($gir_no_doc as $doc) {
                    $gir_no .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $gir_no = 'No Document';
            }

            if (is_array($asset_proof_doc) && count($asset_proof_doc) > 0) {
                $asset_proof = '';
                foreach ($asset_proof_doc as $doc) {
                    $asset_proof .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $asset_proof = 'No Document';
            }

            if (is_array($application_doc) && count($application_doc) > 0) {
                $application = '';
                foreach ($application_doc as $doc) {
                    $application .= '<a href="' . asset('storage/' . $doc) . '" style="margin-left:2px;" target="_blank" download><i data-feather="download"></i></a>';
                }
            } else {
                $application = 'No Document';
            }

            $html = '<tr><td>1</td><td>Aadhar Card</td><td>' . $adhar_card . '</td></tr>
                    <tr><td>2</td><td>PAN/GIR No.</td><td>' . $gir_no . '</td></tr>
                    <tr><td>3</td><td>Vehicle Document</td><td>' . $asset_proof . '</td></tr>
                    <tr><td>4</td><td>Security Document</td><td>' . $application . '</td></tr>';

            $html .= '<tr><td>5</td><td>Promoters</td>';
            if ($documents->termLoanPromoter && $documents->termLoanPromoter->count(0)) {
                $html .= '<td>';
                foreach ($documents->termLoanPromoter as $key => $val) {
                    $html .= '<a href="' . asset('storage/' . $val->domicile_photo) . '" target="_blank" download><i data-feather="download"></i></a> ';
                }
                $html .= '</td>';
            } else {
                $html .= '<td>No Document</td>';
            }
            $html .= '</tr>';

            $html .= '<tr><td>6</td><td>Experience</td>';
            if ($documents->termLoanNetWorth && $documents->termLoanNetWorth->termLoanNetWorthExperience->count(0)) {
                $html .= '<td>';
                foreach ($documents->termLoanNetWorth->termLoanNetWorthExperience as $key => $val) {
                    $html .= '<a href="' . asset('storage/' . $val->doc) . '" target="_blank" download><i data-feather="download"></i></a> ';
                }
                $html .= '</td>';
            } else {
                $html .= '<td>No Document</td>';
            }
            $html .= '</tr>';
            $ass_doc = $documents->ass_doc ? '<a href="' . asset('storage/' . $documents->ass_doc) . '" target="_blank" download><i data-feather="download"></i></a>' : 'No Document';
            $appr_rej_doc = $documents->appr_rej_doc ? '<a href="' . asset('storage/' . $documents->appr_rej_doc) . '" target="_blank" download><i data-feather="download"></i></a>' : 'No Document';
            $statusText = $documents->status == 2 ? 'Approve' : 'Reject';
            $html .= '<tr><td>8</td><td>' . $statusText . '</td><td>' . $appr_rej_doc . '</td></tr>
                      <tr><td>9</td><td>Assessment</td><td>' . $ass_doc . '</td></tr>';

            return response()->json(['doc' => $html]);
        }
    }

    public function loanRecoverySchedule(Request $request)
    {
        $homeLoan = HomeLoan::find($request->rid_loan);
        $rec_intrst = ($homeLoan->bal_princ_amnt * $request->recovery_sentioned) / 100;

        $recoverSch = $request->input('RecoverySchedule', []);
        $sum_of_princ_amnt = 0;
        $sum_of_intrst_amnt = 0;
        $sum_of_total_amnt = 0;
        if (count($recoverSch) > 0) {
            RecoveryScheduleLoan::where('home_loan_id', $request->rid_loan)->delete();
            foreach ($recoverSch['period'] as $index => $period) {
                $sum_of_princ_amnt += $recoverSch['principal_amnt'][$index];
                $sum_of_intrst_amnt += $recoverSch['interest_rate'][$index];
                $sum_of_total_amnt += $recoverSch['total'][$index];
                RecoveryScheduleLoan::create([
                    'home_loan_id' => $request->rid_loan,
                    'period' => $recoverSch['period'][$index] ?? null,
                    'principal_amnt' => $recoverSch['principal_amnt'][$index] ?? null,
                    'interest_rate' => $recoverSch['interest_rate'][$index] ?? null,
                    'recovery_date' => $recoverSch['recovery_date'][$index] ?? null,
                    'total' => $recoverSch['total'][$index] ?? null
                ]);
            }
        }
        $req_status = 'submitted';
        $status = $req_status;

        $data = HomeLoan::updateOrCreate([
            'id' => $request->rid_loan
        ], [
            'recovery_interest' => $request->recovery_interest ?? null,
            'recovery_sentioned' => $request->recovery_sentioned ?? null,
            'recovery_repayment_type' => $request->recovery_repayment_type ?? null,
            'recovery_repayment_period' => $request->recovery_repayment_period ?? null,
            'rec_intrst' => $rec_intrst,
            'recovery_due_date' => $request->recovery_due_date ?? null,
            'approvalStatus' => $status,
            'recovery_pa' => $sum_of_princ_amnt,
            'recovery_ia' => $sum_of_intrst_amnt,
            'recovery_total' => $sum_of_total_amnt,
            'recovery_loan_amount' => $sum_of_total_amnt,
        ]);

        $type = $homeLoan ? $homeLoan->type : null;
        $loan_type = '';
        if ($type == 1) {
            $loan_type = 'home';
        } elseif ($type == 2) {
            $loan_type = 'vehicle';
        } elseif ($type == 3) {
            $loan_type = 'term';
        }
        $logExists = LoanApplicationLog::checkActivityLog($request->rid_loan, $loan_type, self::$user_id, 'recovery');
        if (!$logExists) {
            $loanApplicationLog = LoanApplicationLog::create([
                'loan_application_id' => $request->rid_loan,
                'loan_type' => $loan_type,
                'action_type' => 'recovery',
                'user_id' => self::$user_id,
                'remarks' => null
            ]);
        }

        return redirect("loan/my-application")->with('success', 'Recovery Schedule added successfully!');
    }

    public function getRecoverySchedule(Request $request)
    {
        $recovery_data = HomeLoan::with('recoveryScheduleLoan')->find($request->id);
        $interest_rate = InterestRateScore::where('cibil_score_min', '<=', $recovery_data->ass_cibil)
            ->where('cibil_score_max', '>=', $recovery_data->ass_cibil)
            ->select('interest_rate')
            ->first();
        $interest_rate = $interest_rate->interest_rate;
        $rec_count = $recovery_data->recoveryScheduleLoan->count();
        $html = '';
        if ($recovery_data->recoveryScheduleLoan && $recovery_data->recoveryScheduleLoan->count() > 0) {
            foreach ($recovery_data->recoveryScheduleLoan as $key => $val) {
                $html .= '<tr>
                    <td>' . ($key + 1) . '</td>
                    <td><input type="text" name="RecoverySchedule[period][]" class="form-control mw-100" value=' . $val->period . ' readonly></td>
                    <td><input type="number" name="RecoverySchedule[principal_amnt][]" class="form-control mw-100" value=' . $val->principal_amnt . ' readonly></td>
                    <td><input type="number" name="RecoverySchedule[interest_rate][]" class="form-control mw-100" value=' . $val->interest_rate . ' readonly></td>
                    <td><input type="date" name="RecoverySchedule[recovery_date][]" class="form-control mw-100 recovery_date" value=' . $val->recovery_date . ' readonly></td>
                    <td><input type="number" name="RecoverySchedule[total][]" class="form-control mw-100" value=' . $val->total . ' readonly></td>
                </tr>';
            }
        }

        return response()->json(['recovery_data' => $html, 'loan_data' => $recovery_data, 'interest_rate' => $interest_rate, 'rec_count' => $rec_count]);
    }


    public function loanGetCustomer(Request $request)
    {
        $customer_record = HomeLoan::with([
            'disbursalLoan',
            'recoveryLoan',
            'loanDisbursement',
            'recoveryScheduleLoan' => function ($q) {
                $q->whereNull('recovery_status')
                    ->orderBy('id', 'asc')
                    ->limit(1);
            }
        ])->withSum('recoveryScheduleLoan', 'total')->find($request->id);
        $milestone = '';
        $dis_amnt = '';
        $balance_amount = '';

        if ($customer_record->recoveryScheduleLoan->isEmpty() && !$customer_record->loanDisbursement->isEmpty()) {
            $due_date = Carbon::parse($customer_record->loanDisbursement->created_at)->format('Y-m-d');
        } else if (!$customer_record->recoveryScheduleLoan->isEmpty()) {
            $due_date = Carbon::parse($customer_record->recoveryScheduleLoan[0]->recovery_date)->format('Y-m-d');
        } else {
            $due_date = null;
        }
        // $customer_record->loan_amount
        if (empty($customer_record->recoveryLoan->balance_amount)) {
            $balance_amount = $customer_record->ass_recom_amnt;
        } else {
            $balance_amount = $customer_record->bal_princ_amnt;
        }
        if ($customer_record->disbursalLoan->count() > 0) {
            $milestone = $customer_record->disbursalLoan[0]->milestone;
            $dis_amnt = $customer_record->disbursalLoan[0]->dis_amount;
        }
        return response()->json(['customer_record' => $customer_record, 'milestone' => $milestone, 'dis_amnt' => $dis_amnt, 'balance_amount' => $balance_amount, 'due_date' => $due_date]);
    }

    public function addSettlement()
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        $applicants = HomeLoan::whereNull('settle_status')->where('status', '!=', 3)->where('organization_id', $organization_id)->get();
        $book_type = LoanManagement::getBookType('loan-settlement');
        return view('loan.add_settlement', compact('applicants', 'book_type'));
    }

    public function settlementAddUpdate(Request $request)
    {
        $request->validate([
            'settle_document_no' => ['required'],
            'settle_customer' => 'required',
            'settle_loan_type' => 'required',
            'settle_bal_loan_amnnt' => 'required',
            'settle_prin_bal_amnnt' => 'required',
            'settle_intr_bal_amnnt' => 'required',
            'settle_wo_amnnt' => 'required',
        ], [
            'settle_document_no.required' => 'The Document number is required.',
            'settle_customer.required' => 'The Customer is required.',
            'settle_loan_type.required' => 'The Loan Type is required.',
            'settle_bal_loan_amnnt.required' => 'The Balance Loan Amount is required.',
            'settle_prin_bal_amnnt.required' => 'The Principal Balance Amount is required.',
            'settle_intr_bal_amnnt.required' => 'The Interest Balance Amount is required.',
            'settle_wo_amnnt.required' => 'The Write off Amount is required.'
        ]);

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }
        $data = LoanSettlement::create([
            'home_loan_id' => $request->settle_application_no ?? null,
            'settle_series' => $request->settle_series ?? null,
            'settle_document_no' => $request->settle_document_no ?? null,
            'settle_application_no' => $request->settle_application_no ?? null,
            'settle_bal_loan_amnnt' => $request->settle_bal_loan_amnnt ?? null,
            'settle_prin_bal_amnnt' => $request->settle_prin_bal_amnnt ?? null,
            'settle_intr_bal_amnnt' => $request->settle_intr_bal_amnnt ?? null,
            'settle_amnnt' => $request->settle_amnnt ?? null,
            'settle_wo_amnnt' => $request->settle_wo_amnnt ?? null,
            'remarks' => $request->remarks ?? null,
            'status' => 0,
            'organization_id' => $organization_id
        ]);

        if ($request->hasFile('settle_docs')) {
            $files = $request->file('settle_docs');

            foreach ($files as $file) {
                $filePath = $file->store('loan_documents', 'public');

                LoanSettlementDoc::create([
                    'loan_settlement_id' => $data->id,
                    'doc' => $filePath
                ]);
            }
        }

        if ($data) {
            $settlement_schedule = $request->input('Settlement', []);
            if (count($settlement_schedule) > 0) {
                LoanSettlementSchedule::where('loan_settlement_id', $data->id)->delete();
                foreach ($settlement_schedule['schedule_date'] as $index => $schedule_date) {
                    if ($index == 0) {
                        continue;
                    }
                    LoanSettlementSchedule::create([
                        'loan_settlement_id' => $data->id,
                        'schedule_date' => $settlement_schedule['schedule_date'][$index] ?? null,
                        'schedule_amnt_type' => $settlement_schedule['schedule_amnt_type'][$index] ?? null,
                        'schedule_loan_prcnt' => $settlement_schedule['schedule_loan_prcnt'][$index] ?? null,
                        'schedule_amnt' => $settlement_schedule['schedule_amnt'][$index] ?? null
                    ]);
                }
            }
        }

        $loan_type = explode(' ', $request->settle_loan_type);
        $loanApplicationLog = LoanApplicationLog::create([
            'loan_application_id' => $request->settle_application_no,
            'loan_type' => strtolower($loan_type[0]),
            'action_type' => 'settlement',
            'user_id' => self::$user_id,
            'remarks' => null
        ]);

        $home_loan = HomeLoan::find($request->settle_application_no);
        $home_loan->settle_status = 1;
        $home_loan->save();

        $organization = Organization::getOrganization();
        $book_type = (int) $request->settle_series;
        if ($organization) {
            NumberPattern::incrementIndex($organization->id, $book_type);
        }


        return redirect()->route('loan.settlement')->with('success', 'Settlement Added Successfully!');
    }
    public function SettleApprReject(Request $request)
    {
        if (empty($request->checkedData)) {
            return redirect("loan/settlement")->with('error', 'No Data found for Approve/Reject');
        }
        $app_rej = $request->checkedData;

        $multi_files = [];
        if ($request->hasFile('st_appr_doc') && !$request->has('store_settle_appr_doc')) {
            if ($request->hasFile('st_appr_doc')) {
                $files = $request->file('st_appr_doc');
                foreach ($files as $file) {
                    $filePath = $file->store('loan_documents', 'public');
                    $multi_files[] = $filePath;
                }
            }
            $data = LoanSettlement::updateOrCreate([
                'id' => $app_rej
            ], [
                'settle_appr_status' => $request->st_appr_status ?? null,
                'settle_appr_doc' => (count($multi_files) > 0) ? json_encode($multi_files) : '[]',
                'settle_appr_remark' => $request->st_appr_remark ?? null,
                'status' => $request->st_appr_status ?? null,

            ]);
        } else {
            $store_settle_appr_docData = $request->store_settle_appr_doc;
            $data = LoanSettlement::updateOrCreate([
                'id' => $app_rej
            ], [
                'settle_appr_status' => $request->st_appr_status ?? null,
                'settle_appr_doc' => $store_settle_appr_docData,
                'settle_appr_remark' => $request->st_appr_remark ?? null,
                'status' => $request->st_appr_status ?? null
            ]);
        }

        if ($request->st_appr_status == 1) {
            return redirect("loan/settlement")->with('success', 'Approved Successfully!');
        } else {
            return redirect("loan/settlement")->with('success', 'Rejected Successfully!');
        }
    }

    public function fetchSettleApprove(Request $request)
    {
        $loan_settle = LoanSettlement::where('id', $request->settle_id)->first();
        $html = '';
        if ($loan_settle->settle_appr_doc) {
            $settle_appr_doc_data = json_decode($loan_settle->settle_appr_doc, true);
            $store_settle_appr_doc = [];
            if (count($settle_appr_doc_data) > 0) {
                foreach ($settle_appr_doc_data as $key => $val) {
                    $fileExtension = pathinfo($val, PATHINFO_EXTENSION);
                    $formattedExtension = ucfirst(strtolower($fileExtension));
                    $html .= '<a href="' . asset('storage/' . $val) . '" style="color:green; font-size:12px;" target="_blank" download>' . $formattedExtension . ' File</a></p>';
                    $store_settle_appr_doc[] = $val;
                }
                $jsonEncodedFiles = json_encode($store_settle_appr_doc);
                $html .= '<input type="hidden" name="store_settle_appr_doc" value=\'' . $jsonEncodedFiles . '\' class="form-control" />';
            }
        }
        if ($loan_settle) {
            return response()->json(['success' => 1, 'msg' => 'Loan Disbursement Approved Successfully!', 'loan_settle' => $loan_settle, 'html' => $html]);
        } else {
            return response()->json(['success' => 0, 'msg' => 'Loan Disbursement Not Found!', 'loan_settle' => $loan_settle]);
        }
    }

    public function getPendingDisbursal(Request $request)
    {
        // Step 1: Get disbursement IDs from the LoanDisbursement table
        $disbursementIds = LoanDisbursement::whereNotNull('dis_milestone')
            ->get()
            ->flatMap(function ($disbursement) {
                // Decode the dis_milestone if it's JSON, otherwise use it directly if already an array
                $milestones = is_array($disbursement->dis_milestone)
                    ? $disbursement->dis_milestone
                    : json_decode($disbursement->dis_milestone, true);

                // Ensure $milestones is an array before proceeding
                if (!is_array($milestones)) {
                    return collect(); // Return empty collection if not an array
                }

                // Return the ids from the milestones
                return collect($milestones)->pluck('id');
            });

        // Step 2: Get the organization_id from the authenticated user
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        // Step 3: Perform the query to get the disbursal loans, excluding those already in the disbursement IDs
        $disbursal_loans = ErpLoanAppraisalDisbursal::whereHas('loanAppraisal.loan', function ($query) use ($request, $organization_id) {
            // Apply filters to the related erp_home_loans model
            if ($request->filter_appli_no) {
                $query->where('erp_home_loans.appli_no', 'like', '%' . $request->filter_appli_no . '%');
            }
            if ($request->filter_customer_name) {
                $query->where('erp_home_loans.name', 'like', '%' . $request->filter_customer_name . '%');
            }
            if ($request->filter_loan_type) {
                $query->where('erp_home_loans.type', $request->filter_loan_type);
            }

            // Apply the status and organization_id conditions
            $query->where('organization_id', $organization_id);
        });

        if($request->filter_form=="add")
        $disbursal_loans =$disbursal_loans->whereNotIn('id', $disbursementIds); // Exclude disbursement loans based on the extracted IDs

        $disbursal_loans =$disbursal_loans->with('loanAppraisal.loan')->get();

        // Step 4: Generate HTML output
        $html = '';
        $isFirst = true;
        if (count($disbursal_loans) > 0) {
            foreach ($disbursal_loans as $key => $val) {
                $type = 'Loan';
                if ($val->loanAppraisal->loan->type == 1) {
                    $type = 'Home';
                } elseif ($val->loanAppraisal->loan->type == 2) {
                    $type = 'Vehicle';
                } else {
                    $type = 'Term';
                }
                $radioId = 'customColorRadio' . $key;
                $radioBtn = '<input type="checkbox" id="' . $radioId . '" name="customColorRadio3" class="form-check-input" data-disburse="' . $val->amount . '" data-milestone="' . $val->milestone . '" data-disbursal-id="' . $val->id . '">';

                $html .= '<tr>
                <td>
                <div class="form-check form-check-primary">
                    ' . $radioBtn . '
                </div>
                </td>
                <td id="home_loan_val' . $key . '" style="display: none">' . $val->loanAppraisal->loan->id . '</td>
                <td id="appli_no_val' . $key . '">' . $val->loanAppraisal->loan->appli_no . '</td>
                <td id="dis_date_val' . $key . '">' . $val->created_at->format('Y-m-d') . '</td>
                <td class="fw-bolder text-dark" id="customer_name_val' . $key . '">' . $val->loanAppraisal->loan->name . '</td>
                <td id="type_val' . $key . '">' . $type . '</td>
                <td id="milestone_val' . $key . '">' . $val->milestone . '</td>
                <td id="disburse_val' . $key . '">' . $val->amount . '</td>
                <td>' . $val->loanAppraisal->loan->mobile . '</td>
            </tr>';
            }
        }

        // Return the generated HTML
        return response()->json($html);
    }

    public function setPendingStatus(Request $request)
    {
        try {
            $d_loan = DisbursalLoan::find($request->disbursal_id);
            if ($d_loan) {
                $d_loan->status = 1;
                $d_loan->save();
                return response()->json(['success' => 1, 'msg' => 'Pending Disbursal processed successfully, Click OK to continue.']);
            } else {
                return response()->json(['success' => 0, 'msg' => 'Disbursal not found!']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => 0, 'msg' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function getSeries(Request $request)
    {
        $book_type_id = $request->book_type_id;
        $series = Book::where('booktype_id', $book_type_id)->get();
        $html = '<option value="">Select</option>';

        if ($series) {
            foreach ($series as $val) {
                $html .= '<option value="' . $val->id . '">' . $val->book_name . '</option>';
            }
            return response()->json(['success' => 1, 'msg' => 'Series Fetched successfully', 'html' => $html]);
        } else {
            return response()->json(['success' => 0, 'msg' => 'Series Not Found', 'html' => $html]);
        }
    }
    public function getSeriesBased(Request $request)
    {
        $series_id = $request->series_id;
        $series = HomeLoan::where('booktype_id', $series_id)->get();
        $html = '<option value="">Select</option>';

        if ($series) {
            foreach ($series as $val) {
                $html .= '<option value="' . $val->id . '">' . $val->book_name . '</option>';
            }
            return response()->json(['success' => 1, 'msg' => 'Series Fetched successfully', 'html' => $html]);
        } else {
            return response()->json(['success' => 0, 'msg' => 'Series Not Found', 'html' => $html]);
        }
    }

    public function fetchDisbursementApprove(Request $request)
    {
        $loan_disbursement = LoanDisbursement::where('id', $request->disbursal_id)->first();
        $html = '';
        if ($loan_disbursement->dis_appr_doc) {
            $dis_appr_doc_data = json_decode($loan_disbursement->dis_appr_doc, true);
            $store_dis_appr_doc = [];
            if (count($dis_appr_doc_data) > 0) {
                foreach ($dis_appr_doc_data as $key => $val) {
                    $fileExtension = pathinfo($val, PATHINFO_EXTENSION);
                    $formattedExtension = ucfirst(strtolower($fileExtension));
                    $html .= '<a href="' . asset('storage/' . $val) . '" style="color:green; font-size:12px;" target="_blank" download>' . $formattedExtension . ' File</a></p>';
                    $store_dis_appr_doc[] = $val;
                }
                $jsonEncodedFiles = json_encode($store_dis_appr_doc);
                $html .= '<input type="hidden" name="store_dis_appr_doc" value=\'' . $jsonEncodedFiles . '\' class="form-control" />';
            }
        }
        if ($loan_disbursement) {
            return response()->json(['success' => 1, 'msg' => 'Loan Disbursement Approved Successfully!', 'loan_disbursement' => $loan_disbursement, 'html' => $html]);
        } else {
            return response()->json(['success' => 0, 'msg' => 'Loan Disbursement Not Found!', 'loan_disbursement' => $loan_disbursement]);
        }
    }

    public function getLoanCibil(Request $request)
    {
        $latestInterestRateScore = InterestRateScore::orderBy('id', 'desc')->first();
        if ($latestInterestRateScore) {
            $cibil_score_max = $latestInterestRateScore->cibil_score_max;
            $cibil_score_min = $latestInterestRateScore->cibil_score_min;
            $msg = '';
            if ($cibil_score_min > $request->cibi_score) {
                $msg = 'CIBIL Score should be greater than ' . $cibil_score_min . ' or equal to ' . $cibil_score_min . ' according to interest rate.';
                return response()->json(['success' => 0, 'msg' => $msg, 'value' => '']);
            }
            if ($cibil_score_max < $request->cibi_score) {
                $msg = 'CIBIL Score should be less than or equal to ' . $cibil_score_max . ' according to interest rate.';
                return response()->json(['success' => 0, 'msg' => $msg, 'value' => '']);
            }
            return response()->json(['success' => 1, 'msg' => $msg, 'value' => $request->cibi_score]);
        } else {
            $msg = 'Please Enter Interest rate CIBIL score first.';
            return response()->json(['success' => 0, 'msg' => $msg, 'value' => '']);
        }
    }

    public function getPrincipalInterest(Request $request)
    {
        $loan_id = $request->applicants;
        $recovery_loan = RecoveryScheduleLoan::where('home_loan_id', $loan_id)->whereNull('recovery_status')->first();
        $rec_sent = HomeLoan::find($loan_id);
        $rec_sentioned = $rec_sent->recovery_sentioned ? $rec_sent->recovery_sentioned : $rec_sent->recovery_interest;
        return response()->json(['data' => $recovery_loan, 'rec_sent' => $rec_sentioned]);
    }

    public function getLoanRequests($book_id)
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
        } else {
            $organization_id = 1;
        }

        $request = NumberPattern::where('book_id', $book_id)->where('organization_id', $organization_id)->select('prefix', 'suffix', 'starting_no', 'current_no')->first();

        if (!empty($request)) {
            $requestno = $request->prefix . $request->current_no . $request->suffix;
        } else {
            $requestno = 1;
        }

        return response()->json(['requestno' => $requestno]);
    }

    public function destroy($id)
    {
        try {
            $loan = HomeLoan::findOrFail($id);
            $referenceTables = [
                'erp_loan_addresses' => ['home_loan_id'],
                'erp_loan_reject' => ['loan_application_id'],
                'erp_loan_return' => ['loan_application_id'],
                'erp_loan_employer_details' => ['home_loan_id'],
                'erp_loan_bank_accounts' => ['home_loan_id'],
                'erp_disbursal_loans' => ['home_loan_id'],
                'erp_loan_disbursements' => ['home_loan_id'],
                'erp_loan_incomes' => ['home_loan_id'],
                'erp_loan_other_details' => ['home_loan_id'],
                'erp_loan_other_guarantors' => ['home_loan_id'],
                'erp_loan_proposed_loans' => ['home_loan_id'],
                'erp_loan_guarantor_co_applicants' => ['home_loan_id'],
                'erp_loan_guar_applicants' => ['home_loan_id'],
                'erp_loan_appraisals' => ['loan_id'],
                'erp_loan_approval' => ['loan_application_id'],
                'erp_loan_assessment' => ['loan_application_id'],
                'erp_loan_accept' => ['loan_application_id'],
                'erp_legal_doc' => ['loan_application_id'],
                'erp_loan_process_fee' => ['loan_application_id'],
                'erp_vehicle_bank_securities' => ['vehicle_id'],
                'erp_vehicle_loans' => ['vehicle_id'],
                'erp_loan_vehicle_scheme_costs' => ['vehicle_id'],
                'erp_loan_finance_loan_securities' => ['vehicle_id'],
                'erp_loan_guarantor_parties' => ['vehicle_id'],
                'erp_loan_guarantor_party_addresses' => ['vehicle_id'],

                'erp_loan_vehicle_documents' => ['vehicle_id'],

                'erp_loan_documents' => ['home_loan_id'],

                'erp_term_loan_addresses' => ['term_loan_id'],

                'erp_term_loan_promoters' => ['term_loan_id'],

                'erp_term_loan_constitutions' => ['term_loan_id'],

                'erp_term_loan_finance_means' => ['term_loan_id'],

                'erp_term_loan_net_worths' => ['term_loan_id'],

                'erp_term_loan_documents' => ['term_loan_id'],

                'erp_recovery_loans' => ['home_loan_id'],
                'erp_loan_settlements' => ['home_loan_id'],

                'erp_recovery_schedule_loans' => ['home_loan_id'],
                'loan_application_logs' => ['loan_application_id'],
            ];
            //dd($referenceTables);

            $result = $loan->deleteWithReferences($referenceTables);

            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the Loan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
