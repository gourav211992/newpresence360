<?php

namespace App\Http\Controllers\LoanManagement;
use App\Models\AuthUser;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Models\LoanDisbursement;
use App\Models\HomeLoan;
use App\Models\Organization;
use App\Models\RecoveryLoan;
use Illuminate\Http\Request;
use App\Models\NumberPattern;
use App\Helpers\ConstantHelper;
use App\Models\RecoveryLoanDoc;
use App\Models\Bank;
use App\Models\LoanApplicationLog;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;         
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\FinancialPostingHelper;
use App\Http\Requests\LoanRecoveryRequest;
use Exception;
class LoanRecoveryController extends Controller
{

    public function recovery(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization_id = $user->organization_id;
        if ($request->ajax()) {
            $loans = RecoveryLoan::with('homeLoan.loanAppraisal')
                ->whereNotNull('book_id')
                ->where('organization_id', $organization_id)
                ->select('erp_recovery_loans.*')
                ->whereHas('homeLoan', function ($query) {
                    $query->whereIn('approvalStatus', [ConstantHelper::DISBURSED,ConstantHelper::COMPLETED]);
                })->latest();

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
                    return $loan->homeLoan->loanAppraisal->term_loan ? Helper::formatIndianNumber($loan->homeLoan->loanAppraisal->term_loan) : '-';
                })
                ->addColumn('recovery_amnnt', function ($loan) {
                    return Helper::formatIndianNumber($loan->rec_principal_amnt);
                })
                ->addColumn('rec_principal_amnt', function ($loan) {
                    return Helper::formatIndianNumber($loan->balance_amount);
                })
                ->addColumn('status', function ($loan) {
                    if ($loan->approvalStatus== ConstantHelper::REJECTED)
                        $status = '<span class="badge rounded-pill badge-light-danger badgeborder-radius">' . $loan->approvalStatus . '</span>';
                    else if ($loan->approvalStatus == "Approved" || $loan->approvalStatus == "approved" || $loan->approvalStatus == "approval_not_required" ||   $loan->approvalStatus == "partially_approved")
                        $status = '<span class="badge rounded-pill badge-light-success badgeborder-radius">' . $loan->approvalStatus . '</span>';
                    else
                        $status = '<span class="badge rounded-pill badge-light-info badgeborder-radius">' . $loan->approvalStatus . '</span>';

                    return $status;
                })
                ->editColumn('created_at', function ($data) {
                    $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                    return $formatedDate;
                })
                ->addColumn('action', function ($loan) {
                    return '<td><a href="' . route('loan.recovery_view', ['id' => $loan->id]) . '"><i data-feather="eye"></i></a></td>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } else {
            $loans = RecoveryLoan::with('homeLoan')->where('organization_id', $organization_id)->orderBy('id', 'desc');
        }
        $customer_names = HomeLoan::select('id', 'name')->orderBy('id', 'desc')->get();

        return view('loan.recovery', compact('loans', 'customer_names'));
    }

    public function addRecovery()
    {
       $user = Helper::getAuthenticatedUser();
        $organization_id = $user->organization_id;
        
        $applicants = HomeLoan::with([
            'loanAppraisal.recovery' => function ($query) {
                $query->orderBy('repayment_amount', 'asc'); // Order by repayment_amount in ascending order
            },
            'loanDisbursements',
            'recoveryLoan'
        ])->whereHas('series')
            ->withwhereHas('loanDisbursements')
            ->where('approvalStatus', ConstantHelper::DISBURSED)
            ->whereHas('loanAppraisal.recovery')
            ->where('organization_id', $organization_id)->get();

           
            $parentURL = "loan_recovery";
        
            $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
            if (count($servicesBooks['services']) == 0) {
               return redirect() -> route('/');
           }
           $firstService = $servicesBooks['services'][0];
           $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
            
        $banks = Bank::withDefaultGroupCompanyOrg()->where('status', ConstantHelper::ACTIVE)->get();

        return view('loan.add_recovery', compact('banks', 'applicants', 'book_type'));
    }

    public function viewRecovery($id)
    {

        $data = RecoveryLoan::find($id);

        $applicants = HomeLoan::with([
            'loanAppraisal.recovery' => function ($query) {
                $query->orderBy('repayment_amount', 'asc'); // Order by repayment_amount in ascending order
            },
            'loanDisbursements',
            'recoveryLoan'
        ])

            ->withwhereHas('loanDisbursements', function ($query) {
                $query->where('approvalStatus', ConstantHelper::DISBURSED);
            })->get();



        $loan = HomeLoan::find($data->home_loan_id);
        $parentURL = "loan_recovery";
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
           return redirect() -> route('/');
       }
       $firstService = $servicesBooks['services'][0];
       $book_type = Helper::getBookSeriesNew($firstService -> alias, $parentURL)->get();
        
       $banks = Bank::withDefaultGroupCompanyOrg()->where('status', ConstantHelper::ACTIVE)->get();
        $disbursementIds = json_decode($data->dis_id, true) ?? null;
        if ($disbursementIds)
            $disburse = LoanDisbursement::whereIn('id', $disbursementIds)->get();
        else
            $disburse = "";

            $type = $data->loanable_type;

        $buttons = Helper::actionButtonDisplayForLoan($data->book_id, $data->document_status,$data->id,$data->recovery_amnnt,$data->approval_level,$data->loanable_id,$type);

        return view('loan.view_recovery', compact('loan', 'disburse', 'data', 'banks', 'applicants', 'book_type', 'buttons'));
    }

    public function getPrincipalInterest(Request $request)
    {
        $loan_id = $request->applicants;
        $intrest_amount = 0;

        if ($request->has('exceed_days') && $request->exceed_days !== null) {
            $exceed_days = $request->exceed_days;
            $amount = $request->dis_amount;
            $intrest_rate = HomeLoan::where('id', $loan_id)->with('loanAppraisal', function ($query) use ($loan_id) {
                $query->where('loan_id', $loan_id);
            })->first();
            $intrest_rate = $intrest_rate->loanAppraisal->interest_rate;


            $intrest_amount = ($intrest_rate / 365) * ($exceed_days);

            $intrest_amount = round($amount * ($intrest_amount / 100), 2);
        }

        return response()->json(['amount' => $intrest_amount]);
    }

    public function recoveryAddUpdate(LoanRecoveryRequest $request)
    {
        try {
            $validatedData = $request->validated(); // Validate input
    
            $disbursementData = $request->input('disbursementData');
            $fieldsToSanitize = [
                "recovery_remain",
                "current_settled",
                "loan_amount",
                "dis_amount",
                "rec_amnt",
                "rec_intrst",
                "balance_amount",
                "blnc_amnt",
                "bal_intrst_amnt",
                "recovery_amnnt",
                "settled_amnt",
            ];
    
            foreach ($fieldsToSanitize as $field) {
                if ($request->has($field)) {
                    $request->merge([$field => Helper::removeCommas($request->input($field))]);
                }
            }
    
            if (is_array($disbursementData)) {
                foreach ($disbursementData as $key => $disbursement) {
                    foreach ($disbursement as $subKey => $value) {
                        if (is_string($value)) {
                            $disbursementData[$key][$subKey] = Helper::removeCommas($value);
                        }
                    }
                }
                $request->merge(['disbursementData' => $disbursementData]);
            }
    
            $home_loan = HomeLoan::findOrFail($request->application_no);
            $status = ConstantHelper::ASSESSED;
            $finalJsonData = null;
    
            $balance_amount = ($request->blnc_amnt > $request->recovery_amnnt)
                ? ($home_loan->settle_status == 1 ? ($request->blnc_amnt ?? 0) - ($request->recovery_amnnt ?? 0) : ($request->balance_amount ?? 0))
                : ($home_loan->settle_status == 1 ? 0 : ($request->balance_amount ?? 0));
    
            $updatedDisbursements = [];
            if ($home_loan->settle_status != 1) {
                foreach ($disbursementData as $data) {
                    $recoveryStatus = $data['balance_amount'] > 0 ? 'partial_recover' : 'fully_recovered';
                    $disbur = LoanDisbursement::findOrFail($data['dis_id']);
                    $disbur->update([
                        'recovery_status' => $recoveryStatus,
                        'balance' => $data['balance_amount'],
                        'recovered' => $data['recovered_amount'],
                        'interest' => $data['interest_amount'],
                        'settled_interest' => $data['settled_interest'],
                        'settled_principal' => $data['settled_principal'],
                        'remaining' => $data['remaining'],
                        'recovery_date' => $request->payment_date
                    ]);
    
                    $updatedDisbursements[] = [
                        'dis_id' => $data['dis_id'],
                        'disbursed' => $data['disbursed'],
                        'recovery_status' => $recoveryStatus,
                        'balance' => $data['balance_amount'],
                        'recovered' => $data['recovered_amount'],
                        'interest' => $data['interest_amount'],
                        'settled_interest' => $data['settled_interest'],
                        'settled_principal' => $data['settled_principal'],
                        'remaining' => $data['remaining'],
                        'recovery_date' => $request->payment_date,
                    ];
                }
                $finalJsonData = json_encode($updatedDisbursements);
            }
    
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $validatedData = array_merge($validatedData, [
                'home_loan_id' => $request->application_no ?? null,
                'document_date' => Carbon::now()->format('Y-m-d'),
                'group_id' => $organization->group_id,
                'bal_interest_amnt' => Helper::removeCommas($request->bal_intrst_amnt) ?? null,
                'rec_principal_amnt' => Helper::removeCommas($request->rec_amnt) ?? null,
                'rec_interest_amnt' => Helper::removeCommas($request->rec_intrst) ?? null,
                'balance_amount' => Helper::removeCommas($balance_amount) ?? null,
                'organization_id' => $organization->id,
                'company_id' => $organization->company_id,
                'loanable_id' => $user->auth_user_id,
                'loanable_type' => $user->authenticable_type,
                'approvalStatus' => $status,
                'dis_detail' => $finalJsonData,
                'approvalLevel' => 1,
            ]);
    
            DB::beginTransaction();
            $data = RecoveryLoan::create($validatedData);
            $data->update(['approvalStatus' => Helper::checkApprovalLoanRequired($data->book_id)]);
    
            if ($data->approvalStatus == ConstantHelper::ASSESSED && $data->approvelworkflow->count() > 0) {
                foreach ($data->approvelworkflow as $approver) {
                    if ($approver->user) {
                        LoanNotificationController::notifyLoanRecoverSubmission($approver->user->authUser(), $data);
                    }
                }
            }
    
            $home_loan->update([
                'recovery_pa' => Helper::removeCommas($request->rec_amnt) ?? 0,
                'recovery_ia' => Helper::removeCommas($request->rec_intrst) ?? 0,
                'recovery_total' => (Helper::removeCommas($request->rec_amnt) ?? 0) + (Helper::removeCommas($request->rec_intrst) ?? 0),
                'approvalStatus' => ($request->balance_amount <= 0) ? ConstantHelper::COMPLETED : $home_loan->approvalStatus,
                'recovery_loan_amount' => ($home_loan->settle_status != 1)
                    ? (round($home_loan->recovery_loan_amount, 2) ?? 0) + ($request->current_settled ?? 0)
                    : (round($home_loan->recovery_loan_amount, 2) ?? 0) + ($request->recovery_amnnt ?? 0),
            ]);
    
            if ($request->hasFile('recovery_docs')) {
                foreach ($request->file('recovery_docs') as $file) {
                    RecoveryLoanDoc::create([
                        'recovery_loan_id' => $data->id,
                        'doc' => $file->store('loan_documents', 'public')
                    ]);
                }
            }
    
            LoanApplicationLog::create([
                'loan_application_id' => $request->application_no,
                'loan_type' => strtolower(explode(' ', $request->loan_type)[0]),
                'action_type' => 'recover',
                'user_id' => $user->auth_user_id,
                'remarks' => null
            ]);
    
            DB::commit();
            return redirect()->route('loan.recovery')->with('success', 'Recovery Added Successfully!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'An error occurred while processing the recovery.']);
        }
    }
    
    public function RecoveryApprReject(Request $request)
    {
        if (empty($request->checkedData)) {
            return redirect("loan/recovery")->with('error', 'No Data found for Approve/Reject');
        }
        $app_rej = $request->checkedData;

        $multi_files = [];
        $data = RecoveryLoan::find($app_rej);
        $store_rec_appr_docData = $request->store_rec_appr_doc;


        if ($request->hasFile('rc_appr_doc') && !$request->has('store_rec_appr_doc')) {
            if ($request->hasFile('rc_appr_doc')) {
                $files = $request->file('rc_appr_doc');
                foreach ($files as $file) {
                    $filePath = $file->store('loan_documents', 'public');
                    $multi_files[] = $filePath;
                }
            }
            $store_rec_appr_docData = (count($multi_files) > 0) ? json_encode($multi_files) : '[]';
        }
        $approveDocument = Helper::approveDocument($data->book_id, $data->id, 0, $request->approve_remarks, $multi_files, $data->approvalLevel, $request->rc_appr_status);


        $data = RecoveryLoan::updateOrCreate([
            'id' => $app_rej
        ], [
            'rec_appr_doc' => $store_rec_appr_docData,
            'rec_appr_remark' => $request->rc_appr_remark ?? null,
            'approvalStatus' => $approveDocument['approvalStatus'],
            'approvalLevel' => $approveDocument['nextLevel'] ?? $data->approvalLevel
        ]);
        $creator= AuthUser::find($data->loanable_id);
        if ($request->rc_appr_status == "approve") {
            LoanNotificationController::notifyLoanRecoverApproved($creator->authUser(), $data);
            return redirect("loan/recovery")->with('success', 'Approved Successfully!');
        } else {
            LoanNotificationController::notifyLoanRecoverReject($creator->authUser(), $data);
            return redirect("loan/recovery")->with('success', 'Rejected Successfully!');
        }
    }
    public function fetchRecoveryApprove(Request $request)
    {
        $loan_recovery = RecoveryLoan::where('id', $request->recovery_id)->first();
        $html = '';
        if ($loan_recovery->rec_appr_doc) {
            $rec_appr_doc_data = json_decode($loan_recovery->rec_appr_doc, true);
            $store_rec_appr_doc = [];
            if (count($rec_appr_doc_data) > 0) {
                foreach ($rec_appr_doc_data as $key => $val) {
                    $fileExtension = pathinfo($val, PATHINFO_EXTENSION);
                    $formattedExtension = ucfirst(strtolower($fileExtension));
                    $html .= '<a href="' . asset('storage/' . $val) . '" style="color:green; font-size:12px;" target="_blank" download>' . $formattedExtension . ' File</a></p>';
                    $store_rec_appr_doc[] = $val;
                }
                $jsonEncodedFiles = json_encode($store_rec_appr_doc);
                $html .= '<input type="hidden" name="store_rec_appr_doc" value=\'' . $jsonEncodedFiles . '\' class="form-control" />';
            }
        }
        if ($loan_recovery) {
            return response()->json(['success' => 1, 'msg' => 'Loan Recovery Approved Successfully!', 'loan_recovery' => $loan_recovery, 'html' => $html]);
        } else {
            return response()->json(['success' => 0, 'msg' => 'Loan Recovery Not Found!', 'loan_recovery' => $loan_recovery]);
        }
    }
    public function loanGetCustomer(Request $request)
    {
        $id = $request->id;


        $customer_record = HomeLoan::where('id', $id)
            ->with([
                'loanAppraisal.recovery',
                'recoveryLoan',
                'loanSettlement',
                'loanDisbursements' => function ($query) {
                    $query->where('approvalStatus', ConstantHelper::DISBURSED)
                        ->orderBy('created_at', 'asc');
                }
            ])
            ->first();





        return response()->json(['customer_record' => $customer_record]);
    }
    public function getPostingDetails(Request $request)
    {
            try{
                $data = FinancialPostingHelper::loanRecoverVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "get",$request->remarks);
            return response() -> json([
                'status' => 'success',
                'data' => $data
            ]);
        }
        catch(Exception $e){
                return response() -> json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }

    }
    public function postPostingDetails(Request $request)
    {
        try{
            $data = FinancialPostingHelper::loanRecoverVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "post",$request->remarks);
            if ($data['status']) {
                    $pv = RecoveryLoan::find($request -> document_id);
                    $pv->approvalStatus = ConstantHelper::POSTED;
                    $pv->save();
                }
                return response() -> json([
                    'status' => 'success',
                    'data' => $data
                ]);
               
        }
        catch(Exception $e){
            return response() -> json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

    }
}
