<?php

namespace App\Http\Controllers\refined_index;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\ErpTransaction;
use App\Models\Organization;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class IndexController extends Controller
{
    public function approvals(Request $request)
    {
        $documentStatus = ['submitted', 'partially_approved'];
        $user = Helper::getAuthenticatedUser();
        $redirectUrl = route('riv.approvals');

        // Query the transactions dynamically
        $data = ErpTransaction::whereIn('document_status', $documentStatus)
        ->whereExists(function ($query) use ($user) {
            $query->select(DB::raw(1))
                ->from('erp_book_levels')
                ->join('erp_approval_workflows', 'erp_approval_workflows.book_level_id', '=', 'erp_book_levels.id')
                ->whereColumn('erp_book_levels.organization_id', 'erp_transactions.organization_id')
                ->whereColumn('erp_book_levels.book_id', 'erp_transactions.book_id')
                ->whereColumn('erp_book_levels.level', 'erp_transactions.approval_level')
                ->where('erp_approval_workflows.user_id', $user->auth_user_id)
                ->whereNotExists(function ($subquery) {
                    $subquery->select(DB::raw(1))
                        ->from('erp_document_approvals')
                        ->whereColumn('document_type', 'erp_transactions.document_type')
                        ->whereColumn('document_id', 'erp_transactions.document_id')
                        ->whereColumn('revision_number', 'erp_transactions.revision_number')
                        ->where('approval_type', 'approve')
                        ->whereColumn('user_id', 'erp_approval_workflows.user_id');
                });
        })
        ->orderBy('created_at', 'desc')
        ->get();
        // Handle Ajax request for DataTables
        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-secondary';
                    $displayStatus = ucfirst($row->document_status ?? 'Unknown');
                    $editRoute = route(ConstantHelper::SERVICE_ALIAS_VIEW_ROUTE[$row->book->service->service->alias], ['id' => $row->document_id,'type'=>($row->document_type == "po") ? "purchase-order" : $row->document_type]);
                    
                    return "
                    <div style='text-align:right;'>
                        <span class='badge rounded-pill $statusClass badgeborder-radius'>$displayStatus</span>
                        <div class='dropdown' style='display:inline;'>
                            <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                                <i data-feather='more-vertical'></i>
                            </button>
                            <div class='dropdown-menu dropdown-menu-end'>
                                <a class='dropdown-item' href='" . $editRoute . "'>
                                    <i data-feather='edit-3' class='me-50'></i>
                                    <span>View/ Edit Detail</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    ";
                })
                ->addColumn('document_type', fn($row) => ConstantHelper::SERVICE_LABEL[$row->document_type] ?? 'Unknown')
                ->addColumn('book_name', fn($row) => $row->book_code ?? 'N/A')
                ->addColumn('document_number', fn($row) => $row->document_number ?: 'N/A')
                ->addColumn('document_type', fn($row) => strtoupper($row->document_type) ?: 'N/A')
                ->editColumn('document_date', fn($row) => $row->document_date ? date('Y-m-d', strtotime($row->document_date)) : 'N/A')
                ->editColumn('revision_number', fn($row) => strval($row->revision_number ?? '0'))
                ->addColumn('party_name', fn($row) => $row->party_code ?? 'NA')
                ->addColumn('currency', fn($row) => $row->currency_code ?? 'NA')
                ->editColumn('total_amount', fn($row) => number_format($row->total_amount, 2))
                ->editColumn('submitted_by', function ($row) {
                    $user = AuthUser::find($row->created_by);
                    return $user ? $user->name : 'N/A';
                })
                ->rawColumns(['document_status'])
                ->make(true);
        }

        return view('riv.approvals.index', [
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function requests(Request $request)
     // submitted partially_approved draft rejected mapped with created_by user
    {   $documentStatus = ['draft','partially_approved','submitted','rejected'];
        $user = Helper::getAuthenticatedUser();
        $redirectUrl = route('riv.requests');
        // Query the transactions dynamically
        $data = ErpTransaction::withDefaultGroupCompanyOrg()->whereIn('document_status', $documentStatus)->where('created_by',$user->auth_user_id)->orderBy('created_at', 'desc')->get();
        // Handle Ajax request for DataTables
        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('document_status', function ($row) {
                    $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-secondary';
                    $displayStatus = ucfirst($row->document_status ?? 'Unknown');
                    $editRoute = route(ConstantHelper::SERVICE_ALIAS_VIEW_ROUTE[$row->book->service->service->alias], ['id' => $row->document_id,'type'=>($row->document_type == "po") ? "purchase-order" : $row->document_type]);
                    return "
                    <div style='text-align:right;'>
                        <span class='badge rounded-pill $statusClass badgeborder-radius'>$displayStatus</span>
                        <div class='dropdown' style='display:inline;'>
                            <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                                <i data-feather='more-vertical'></i>
                            </button>
                            <div class='dropdown-menu dropdown-menu-end'>
                                <a class='dropdown-item' href='" . $editRoute . "'>
                                    <i data-feather='edit-3' class='me-50'></i>
                                    <span>View/ Edit Detail</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    ";
                })
                ->addColumn('document_type', fn($row) => ConstantHelper::SERVICE_LABEL[$row->document_type] ?? 'Unknown')
                ->addColumn('book_name', fn($row) => $row->book_code ?? 'N/A')
                ->addColumn('document_type', fn($row) => strtoupper($row->document_type) ?: 'N/A')
                ->addColumn('document_number', fn($row) => $row->document_number ?: 'N/A')
                ->editColumn('document_date', fn($row) => $row->document_date ? date('Y-m-d', strtotime($row->document_date)) : 'N/A')
                ->editColumn('revision_number', fn($row) => strval($row->revision_number ?? '0'))
                ->addColumn('party_name', fn($row) => $row->party_code ?? 'NA')
                ->addColumn('currency', function($row)  {
                    $org = Organization::find($row->organization_id);
                    return $row->currency_code ?? $org->currency_code ?? 'NA';
                })                
                ->editColumn('total_amount', fn($row) => number_format($row->total_amount, 2))
                ->rawColumns(['document_status'])
                ->make(true);
        }

        return view('riv.submitted.index', [
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function approved_view(){
        return view('riv.approval.index');
    }
}
// request me submitted or paritally approved aur mera approval pending ho 