<?php

namespace App\Http\Controllers\refined_index;

use App\Helpers\{ConstantHelper, Helper, RefinedIndex\indexFilterHelper, InventoryHelper};
use App\Http\Controllers\Controller;
use App\Models\{AttributeGroup, AuthUser, Book, Category, ErpTransaction, Item, Organization};
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class IndexController extends Controller
{
    public function approvals(Request $request)
    {
        return $this->handleTransactionView(
            request: $request,
            view: 'riv.approvals.index',
            redirectRoute: 'riv.approvals',
            documentStatuses: ['submitted', 'partially_approved'],
            userFilter: fn($query, $user) => $query->whereExists(function ($query) use ($user) {
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
            }),
            excludeOwn: true
        );
    }

    public function requests(Request $request)
    {
        return $this->handleTransactionView(
            request: $request,
            view: 'riv.submitted.index',
            redirectRoute: 'riv.requests',
            documentStatuses: ['draft','partially_approved','submitted','rejected'],
            userFilter: fn($query, $user) => $query->where('created_by', $user->auth_user_id)
        );
    }

    public function postings(Request $request)
    {
        $book_ids = Book::withDefaultGroupCompanyOrg()->whereHas('parameters', function ($query) {
            $query->where('parameter_name', 'gl_posting_required')
                ->whereJsonContains('parameter_value', 'yes');
        })->pluck('id')->toArray();

        return $this->handleTransactionView(
            $request,
            'riv.posting.index',
            'riv.postings',
            ['approved','approval_not_required','closed'],
            fn($query) =>
                $query->whereIn('book_id', Book::withDefaultGroupCompanyOrg()
                    ->whereHas('parameters', function ($query) {
                        $query->where('parameter_name', 'gl_posting_required')
                            ->whereJsonContains('parameter_value', 'yes');
                    })->pluck('id')->toArray()
                )->when($request->services, fn($q) => $q->where('customer_id', $request->customer_id))
        );
    }

    private function handleTransactionView(Request $request, string $view, string $redirectRoute, array $documentStatuses, callable $userFilter = null, bool $excludeOwn = false)
    {
        $user = Helper::getAuthenticatedUser();
        $accessible_locations = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray();
        $selectedfyYear = Helper::getFinancialYear(Carbon::now()->format('Y-m-d'));

        $query = ErpTransaction::withDefaultGroupCompanyOrg()
            ->whereIn('document_status', $documentStatuses)
            ->whereBetween('document_date', [$selectedfyYear['start_date'], $selectedfyYear['end_date']])
            ->orderBy('created_at', 'desc');

        if ($userFilter) {
            $query = $userFilter($query, $user);
        }

        if ($excludeOwn) {
            $query = $query->where('created_by', '!=', $user->auth_user_id);
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('document_status', fn($row) => $this->renderStatusWithActions($row))
                ->addColumn('document_type', fn($row) => ConstantHelper::SERVICE_LABEL[$row->document_type ?? $row->book->service->service->alias] ?? '')
                ->addColumn('book_name', fn($row) => $row->book_code ?? 'N/A')
                ->addColumn('document_number', fn($row) => $row->document_number ?: 'N/A')
                ->editColumn('document_date', fn($row) => $row->document_date ? date('Y-m-d', strtotime($row->document_date)) : 'N/A')
                ->editColumn('revision_number', fn($row) => strval($row->revision_number ?? '0'))
                ->addColumn('party_name', fn($row) => $row->party_code ?? 'NA')
                ->addColumn('currency', fn($row) => $row->currency_code ?? Organization::find($row->organization_id)?->currency_code ?? 'NA')
                ->editColumn('total_amount', fn($row) => number_format($row->total_amount, 2))
                ->editColumn('submitted_by', fn($row) => AuthUser::find($row->created_by)?->name ?? 'N/A')
                ->rawColumns(['document_status'])
                ->make(true);
        }

        return view($view, [
            'filterArray' => indexFilterHelper::Index_FILTERS,
            'redirect_url' => route($redirectRoute),
        ]);
    }

    private function renderStatusWithActions($row): string
    {
        $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$row->document_status] ?? 'badge-secondary';
        $displayStatus = ucfirst($row->document_status ?? '');
        $alias = $row->book->service->service->alias;
        $routeName = ConstantHelper::SERVICE_ALIAS_VIEW_ROUTE[$alias];
        $documentType = $row->document_type === 'po' ? 'purchase-order' : $row->document_type;
        $routeParams = [    
            'id' => $row->document_id,
            'type' => $documentType,
            'payment' => $row->document_id,
            'voucher' => $row->document_id,
            'receipt' => $row->document_id,
        ];

        $editRoute = route($routeName, $routeParams);

        return "
            <div style='text-align:right;'>
                <span class='badge rounded-pill {$statusClass} badgeborder-radius'>{$displayStatus}</span>
                <div class='dropdown' style='display:inline;'>
                    <button type='button' class='btn btn-sm dropdown-toggle hide-arrow py-0 p-0' data-bs-toggle='dropdown'>
                        <i data-feather='more-vertical'></i>
                    </button>
                    <div class='dropdown-menu dropdown-menu-end'>
                        <a class='dropdown-item' href='{$editRoute}'>
                            <i data-feather='edit-3' class='me-50'></i>
                            <span>View/ Edit Detail</span>
                        </a>
                    </div>
                </div>
            </div>
        ";
    }
}