<?php

namespace App\Http\Controllers\refined_index;

use App\Helpers\{ConstantHelper, Helper, RefinedIndex\indexFilterHelper, InventoryHelper};
use App\Helpers\FinancialPostingHelper;
use App\Http\Controllers\Controller;
use App\Models\{AttributeGroup, AuthUser, Book, Category, ErpTransaction , Item, Organization};
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Route;
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
            ->where(function ($q) use ($accessible_locations) {
                $q->whereIn('location_id', $accessible_locations)
                ->orWhereNull('location_id');
            })
            ->orderBy('created_at', 'desc');
            if ($userFilter) {
                $query = $userFilter($query,$user);
            }

        if ($excludeOwn) {
            $query = $query->where('created_by', '!=', $user->auth_user_id);
        }

        if ($request->ajax()) {
            if (in_array($redirectRoute, ['riv.approvals', 'riv.postings'])) {
                $dataTable = DataTables::of($query)
                    ->addColumn('checkbox', function($row) {
                        static $rowCount = 0;
                        $rowCount++;
                        $id = "Email_{$rowCount}";
                        return "<div class=\"form-check form-check-primary custom-checkbox\">
                                    <input type=\"checkbox\" class=\"form-check-input transaction-select-checkbox\" id=\"{$row->document_id}\" alias=\"{$row->book->service->service->alias}\" data-id=\"{$row->id}\">
                                    <label class=\"form-check-label\" ></label>
                                </div>";
                    });
            } else {
                $dataTable = DataTables::of($query)->addIndexColumn();
            }

            return $dataTable
                ->editColumn('document_status', fn($row) => $this->renderStatusWithActions($row))
                ->addColumn('document_type', fn($row) => ConstantHelper::SERVICE_LABEL[$row->document_type ?? $row->book->service->service->alias] ?? '')
                ->addColumn('book_name', fn($row) => $row->book_code ?? ($row->book->book_code??'N/A'))
                ->addColumn('document_number', fn($row) => $row->document_number ?: 'N/A')
                ->editColumn('document_date', fn($row) => $row->document_date ? date('Y-m-d', strtotime($row->document_date)) : 'N/A')
                ->editColumn('revision_number', fn($row) => strval($row->revision_number ?? '0'))
                ->addColumn('party_name', fn($row) => $row->party_code ?? 'NA')
                ->addColumn('currency', fn($row) => $row->currency_code ?? Organization::find($row->organization_id)?->currency_code ?? 'NA')
                ->editColumn('total_amount', fn($row) => number_format($row->total_amount, 2))
                ->editColumn('submitted_by', fn($row) => AuthUser::find($row->created_by)?->name ?? 'N/A')
                ->rawColumns(['document_status', 'checkbox'])
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

        // All available route params
        $allRouteParams = [
            'id' => $row->document_id,
            'type' => $documentType,
            'payment' => $row->document_id,
            'voucher' => $row->document_id,
            'receipt' => $row->document_id,
        ];

        // Get required parameter names for this route
        $route = Route::getRoutes()->getByName($routeName);
        $routeParams = [];

        if ($route) {
            preg_match_all('/\{(\w+?)\}/', $route->uri(), $matches);
            $requiredParams = $matches[1]; // ['id', 'type'] for example

            foreach ($requiredParams as $param) {
                if (array_key_exists($param, $allRouteParams)) {
                    $routeParams[$param] = $allRouteParams[$param];
                }
            }
        }

        // Generate the route only with required params
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
    public function bulkapprovals(Request $request)
    {
        $selectedIds = $request->input('ids', []);
        $results = [];
        $modelCheck = [];
        foreach ($selectedIds as $item) {
            if (!isset($item['document_id'], $item['alias'])) {
                continue;
            }
            $baseNamespace = 'App\Models\\';
            $className = ConstantHelper::SERVICE_ALIAS_MODELS[$item['alias']] ?? null;
            $modelClass = $baseNamespace . $className;

            if ($modelClass && class_exists($modelClass)) {
                $data = $modelClass::find($item['document_id']);
                $approveDocument = Helper::approveDocument($data?->book?->id ?? $data->book_id, $item['document_id'], $data->revision_number , '', [], $data->approval_level, $request -> actionType , 0, $modelClass);
                if ($approveDocument['message']) {
                    $results[] = [
                        'document_id' => $item['document_id'],
                        'message' => $approveDocument['message'],
                        'status' => 'error'
                    ];
                } else {
                    $results[] = [
                        'document_id' => $item['document_id'],
                        'message' => 'Document approved successfully',
                        'status' => 'success'
                    ];
                    $document_status = $approveDocument['approvalStatus'] ?? $data -> document_status;
                    $data->document_status = $document_status;
                }
                $data -> save();
            }
        }
        return response()->json(['data' => $results]);
    }

    public function bulkpostings(Request $request)
    {
        $selectedIds = $request->input('ids', []);
        $results = [];

        foreach ($selectedIds as $item) {
            if (!isset($item['document_id'], $item['alias'])) {
                continue;
            }

            $baseNamespace = 'App\\Models\\';
            $className = ConstantHelper::SERVICE_ALIAS_MODELS[$item['alias']] ?? null;
            $modelClass = $baseNamespace . $className;

            if ($modelClass && class_exists($modelClass)) {
                $data = $modelClass::find($item['document_id']);

                if ($data) {
                    $bookId = $data->book_id;
                    $docId = $data -> id;
                    $label=ConstantHelper::SERVICE_LABEL[$data->book->service->alias];
                    $postResult = FinancialPostingHelper::financeVoucherPosting($bookId, $docId, 'post');
                    if (!empty($postResult['message'])) {
                        $results[] = [
                            'document_type' => $label,
                            'document_id' => $data->book->book_code."-".$data -> document_number,
                            'message' => $postResult['message'],
                            'status' => 'error'
                        ];
                    } else {
                        $results[] = [
                            'document_type' => $label,
                            'document_id' => $data->book->book_code."-".$data -> document_number,
                            'message' => 'Document posted successfully',
                            'status' => 'success'
                        ];
                    }
                } else {
                    $results[] = [
                        'document_type' => $label,
                        'document_id' => $item['document_id'],
                        'message' => 'Document not found',
                        'status' => 'error'
                    ];
                }
            } else {
                $results[] = [
                    'document_type' => $label,
                    'document_id' => $item['document_id'],
                    'message' => 'Model class not found or invalid',
                    'status' => 'error'
                ];
            }
        }

        return response()->json(['data' => $results]);
    }


    public function bulkrequests(Request $request)
    {
        $selectedIds = $request->input('ids', []);
        $results = [];

        foreach ($selectedIds as $item) {
            if (!isset($item['document_id'], $item['alias'])) {
                continue;
            }
            $modelClass = ConstantHelper::SERVICE_ALIAS_MODELS[$item['alias']] ?? null;
            if ($modelClass && class_exists($modelClass)) {
                $results[] = $modelClass::where('document_id', $item['document_id'])->first();
            }
        }

        return response()->json(['data' => $results]);
    }
}