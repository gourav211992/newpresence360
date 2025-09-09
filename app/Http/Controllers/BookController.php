<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Helpers\DynamicFieldHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\Helper;
use App\Helpers\ServiceParametersHelper;
use App\Models\AuthUser;
use App\Models\Item;
use App\Models\BookDynamicField;
use App\Models\DynamicField;
use App\Models\DynamicFieldDetail;
use App\Models\ErpSaleOrder;
use App\Models\Group;
use App\Models\OrganizationBookParameter;
use App\Models\OrganizationService;
use App\Models\OrganizationServiceParameter;
use App\Models\Service;
use App\Models\ServiceParameter;
use Exception;
use Illuminate\Http\Request;
use App\Models\BookType;
use App\Models\Book;
use App\Models\AreaMaster;
use App\Models\Employee;
use App\Models\NumberPattern;
use App\Models\ApprovalWorkflow;
use App\Models\AmendmentWorkflow;
use App\Models\AmendmentWorkflowUsers;
use App\Models\BookLevel;
use App\Models\ErpService;
use App\Models\User;
use App\Models\SubLocation;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use stdClass;
use Yajra\DataTables\DataTables;
use App\Helpers\ReManufacturing\RepairOrder\Constants as RepConstants;
use App\Models\ErpOrganizationService;

class BookController extends Controller
{
    public function get_codes(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();

        $data = Book::where('booktype_id', $request->bookType)
            ->where('group_id', $organization->group_id)
            ->select('id', 'book_code as code', 'book_name')
            ->get();
        return response()->json($data);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Book::withDefaultGroupCompanyOrg();
            $books = $query->orderBy('id','desc');
            return DataTables::of($books)
            ->addIndexColumn()
            ->addColumn('service_name', function ($row) {
                return $row -> org_service ?-> name;
            })
            ->editColumn('book_code', function ($row) {
                return $row -> book_code;
            })
            ->editColumn('book_name', function ($row) {
                return $row -> book_name;
            })
            ->editColumn('manual_entry', function ($row) {
                return $row -> manual_entry ? 'Yes' : 'No';
            })
            ->editColumn('status', function ($row) {
                return $row -> status;
            })
            ->editColumn('status', function ($row) {
                $bookStatusClass = (strtolower($row -> status)) == ConstantHelper::ACTIVE ? 'success' : 'danger';
                $displayStatus = (ucfirst($row -> status));
                return "<span class='badge rounded-pill badge-light-".$bookStatusClass." badgeborder-radius'>$displayStatus</span>";
            })
            ->addColumn('action', function ($row) {
                return '
                <div class="dropdown">
                    <button type="button"
                            class="btn btn-sm dropdown-toggle hide-arrow py-0"
                            data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="'. route('bookEdit', $row -> id) .'">
                                <i data-feather="edit-3" class="me-50"></i>
                                <span>Edit</span>
                            </a>
                        </div>
                    </div>
            ';})
            ->rawColumns(['status','action'])
            ->make(true);
        }
        return view('book.index');
    }

    public function book_create()
    {
        $authUser = Helper::getAuthenticatedUser();
        $userType = Helper::userCheck()['type'];
        $services = OrganizationService::withDefaultGroupCompanyOrg()->get();
        //Get Org Access of logged in USER
        $authOrganization = Organization::find($authUser -> organization_id);
        $groupId = $authOrganization ?-> group_id;
        if ($authUser -> user_type === ConstantHelper::IAM_SUPER_ADMIN && $groupId) {
            $orgIds = Organization::where('group_id', $groupId) -> pluck('id') -> toArray();
        } else {
            $orgIds = $authUser -> organizations() -> pluck('organizations.id') -> toArray();
        }
        array_push($orgIds, $authUser?->organization_id);
        //Get Company according to Org
        $companyIds = Organization::whereIn('id', $orgIds)->where('status', ConstantHelper::ACTIVE)->get()->pluck('company_id');
        $companies = OrganizationCompany::whereIn('id', $companyIds)->with('organizations', function ($subQuery) use ($orgIds) {
            $subQuery->whereIn('id', $orgIds);
        })->get();
        $people = [];
        $dynamicFields = DynamicField::select('id', 'name') -> withDefaultGroupCompanyOrg() -> whereHas('items')
        -> where('status', ConstantHelper::ACTIVE) -> get();
        return view('book.create-book', compact('companies', 'people', 'services', 'dynamicFields'));
    }

    public function store(Request $request)
    {

        $authUser = Helper::getAuthenticatedUser();
        $org = Organization::find($authUser->organization_id);
        $company = OrganizationCompany::find($org?->company_id);
        $group = Group::find($company?->group_id);
        $request->validate([
            'org_service_id' => 'required|exists:erp_organization_services,id',
            'manual_entry' => ['required','string', Rule::in("yes", "no")],
            'book_code' => [
                Rule::unique('erp_books', 'book_code')->where(function ($query) use ($group, $request) {
                    $query->where('group_id', $group?->id) -> where('manual_entry', $request -> manual_entry == "yes" ? 1 : 0);
                }),
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/'
            ],
            'book_name' => 'required|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
            'company_id' => 'required_if:manual_entry,yes|array|min:1',
            'company_id.*' => 'required_if:manual_entry,yes|numeric|integer',
            'organization_id' => 'required_if:manual_entry,yes|array|min:1',
            'organization_id.*' => 'required_if:manual_entry,yes|numeric|integer',
            'series_numbering' => 'required_if:manual_entry,yes|array|min:1',
            'series_numbering.*' => 'required_if:manual_entry,yes|in:Auto,Manually',
            'params' => 'array',
            'param_ids' => 'array',
            'param_names' => 'array',
            'gl_params' => 'array',
            'gl_param_ids' => 'array',
            'gl_param_names' => 'array',
            //Level Company Id
            'level_company_id' => 'array',
            'level_company_id.*' => 'required_if:manual_entry,yes|numeric|integer',

        ]);

        if (!empty($request->input('level_company_id'))) {
            $request->validate([
                //Orgs
                'level_organization_id' => 'required|array',
                'level_organization_id.*' => 'required|numeric|integer',
            ]);
        }
        if (!empty($request->input('level_organization_id'))) {
            $request->validate([
                //Approvers
                'user' => 'required|array',
                'user.*' => 'required|array',
                'user.*.*' => 'required|numeric|integer'
            ], [
                'user.required' => 'Approvers must be present in every level'
            ]);
        }
        if (!empty($request->input('user'))) {
            $request->validate([
                //Min Value
                'min_value' => 'required|array',
                'min_value.*' => 'required|numeric',
                //Min Value
                'rights' => 'required|array',
                'rights.*' => 'required|in:all,anyone'
            ]);
            if (count($request->input('level_company_id')) !== count($request->input('level_organization_id')) && count($request->input('level_organization_id')) !== count($request->input('user'))) {
                return back()->withErrors(['user' => 'All fields should be present']);
            }
        }

        if (!empty($request->params) && !empty($request->param_ids) && !empty($request->param_names)) {
            $request->validate([
                'param_ids.*' => 'required|numeric|integer',
                'param_names.*' => 'required|string',
            ]);
        }

        try {

            if (!empty($request->input('organization_id'))) {
                if (count($request->organization_id) !== count(array_unique($request->organization_id))) {
                    return response()->json([
                        'message' => 'Duplicate entries in Number pattern for same location are not allowed!',
                        'error' => "",
                    ], 422);
                }
            }

            // if (!empty($request->input('level_organization_id'))) {
            //     if (count($request -> level_organization_id) !== count(array_unique($request -> level_organization_id))) {
            //         return response()->json([
            //             'message' => 'Duplicate entries in Approval Workflow for same location are not allowed!',
            //             'error' => "",
            //         ], 422);
            //     }
            // }

            if (!empty($request->input('amendment_organization_id'))) {
                if (count($request->amendment_organization_id) !== count(array_unique($request->amendment_organization_id))) {
                    return response()->json([
                        'message' => 'Duplicate entries in Amendment Workflow for same location are not allowed!',
                        'error' => "",
                    ], 422);
                }
            }

            DB::beginTransaction();

            $organization = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->first();

            $insert = new Book;
            $insert->booktype_id = $request->booktype_id;
            $insert->book_code = $request->book_code;
            $insert->book_name = $request->book_name;
            $insert->manual_entry = $request->manual_entry === "yes" ? 1 : 0;
            $insert->org_service_id = $request->org_service_id;
            $orgService = OrganizationService::find($request->org_service_id);
            if (isset($orgService)) {
                $insert->service_id = $orgService->service_id;
            }
            $insert->status = $request->status;
            $insert->group_id = $organization->group_id;
            // $insert->company_id = $organization->company_id;
            // $insert->organization_id = $organization->id;
            $insert->save();

            // Save number patterns
            if ($request -> manual_entry === "yes") {
                foreach ($request->company_id as $key => $value) {
                    NumberPattern::create([
                        'book_id' => $insert->id,
                        'company_id' => $request->company_id[$key],
                        'organization_id' => $request->organization_id[$key],
                        'series_numbering' => $request->series_numbering[$key],
                        'reset_pattern' => $request->reset_pattern[$key] ?? null,
                        'prefix' => $request->prefix[$key] ?? null,
                        'starting_no' => $request->starting_no[$key] ?? null,
                        'suffix' => $request->suffix[$key] ?? null,
                        'current_no' => $request->starting_no[$key] ?? 1,
                    ]);
                }

                // Save approval workflows with individual users
                if ($request->level_company_id) {
                    foreach ($request->level_company_id as $key => $level_company_id) {

                        // Insert levels
                        $levelInsert = new BookLevel;
                        $levelInsert->book_id = $insert->id;
                        $levelInsert->level = $request->level[$key];
                        //In case of Master type services -> this should be always 0
                        if ($insert -> master_service ?-> type === ConstantHelper::ERP_MASTER_SERVICE_TYPE) {
                            $levelInsert->min_value = 0;
                        } else {
                            $levelInsert->min_value = $request->min_value[$key];
                        }
                        // $levelInsert->max_value = $request->max_value[$key];
                        $levelInsert->rights = $request->rights[$key];
                        $levelInsert->company_id = $request->level_company_id[$key];
                        $levelInsert->organization_id = $request->level_organization_id[$key];
                        $levelInsert->save();

                        // Loop through each user info for that level
                        foreach ($request->user[$key] as $user_info) {
                            // Split user info into user_id and user_type
                            // list($user_id, $user_type) = explode('|', $user_info);

                            ApprovalWorkflow::create([
                                'book_id' => $insert->id,
                                'company_id' => $request->level_company_id[$key],
                                'book_level_id' => $levelInsert->id,
                                'organization_id' => $request->level_organization_id[$key],
                                'user_id' => $user_info,
                                'user_type' => 'employee'
                            ]);
                        }
                    }
                }

                if ($request->amendment_company_id) {
                    foreach ($request->amendment_company_id as $key => $amendment_company_id) {

                        // Insert levels
                        $amendmentInsert = new AmendmentWorkflow;
                        $amendmentInsert->book_id = $insert->id;
                        //In case of Master type services -> min value should always be 0 and aaproval required false
                        if ($insert -> master_service ?-> type === ConstantHelper::ERP_MASTER_SERVICE_TYPE) {
                            $amendmentInsert->min_value = 0;
                            $amendmentInsert->approval_required = 0;
                        } else {
                            $amendmentInsert->min_value = $request->amendment_min[$key];
                            $amendmentInsert->approval_required = $request->approval_req[$key] == 'yes' ? 1 : 0;
                        }
                        $amendmentInsert->max_value = 0;
                        $amendmentInsert->company_id = $request->amendment_company_id[$key];
                        $amendmentInsert->organization_id = $request->amendment_organization_id[$key];
                        $amendmentInsert->save();

                        // Loop through each user info for that level
                        foreach ($request->amendment_user[$key] as $user_info) {
                            // Split user info into user_id and user_type
                            // list($user_id, $user_type) = explode('|', $user_info);

                            AmendmentWorkflowUsers::create([
                                'book_id' => $insert->id,
                                'company_id' => $request->amendment_company_id[$key],
                                'amendment_workflow_id' => $amendmentInsert->id,
                                'organization_id' => $request->amendment_organization_id[$key],
                                'user_id' => $user_info,
                                'user_type' => 'employee'
                            ]);
                        }
                    }
                }

                //Create a financial Book code (if applicable)
                $orgService = OrganizationService::find($request -> org_service_id);
                if (isset($orgService)) {
                    $financialServiceAlias = ServiceParametersHelper::getFinancialServiceAlias($orgService -> service -> alias);
                    if (isset($financialServiceAlias)) {
                        $financialService = Service::where('alias', $financialServiceAlias) -> first();
                        $orgFinancialService = OrganizationService::where('alias', $financialServiceAlias) -> where('group_id', $organization -> group_id) -> first();
                        if (isset($financialService) && isset($orgFinancialService)) {
                            Book::create([
                                'org_service_id' => $orgFinancialService -> id,
                                'service_id' => $financialService -> id,
                                'book_code' => $request -> book_code,
                                'book_name' => $request -> book_code,
                                'status' => ConstantHelper::ACTIVE,
                                'group_id' => $organization -> group_id,
                                'company_id' => null,
                                'organization_id' => null,
                                'manual_entry' => 0
                            ]);
                        }
                    }
                }

                if (isset($request->params) && isset($request->param_names) && isset($request->param_ids))
                {
                    $referenceFromIndex = array_search(ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM, $request->param_names);

                    if (isset($request -> params) && isset($request -> param_names) && isset($request -> param_ids))
                    {
                        //Retrieve the reference from parameter value
                        $referenceFromIndex = array_search(ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM, $request -> param_names);
                        if ($referenceFromIndex !== false) {
                            $referenceFrom = isset($request->params[$referenceFromIndex]) ? $request->params[$referenceFromIndex] : [];
                            if (count($referenceFrom) == 0) {
                                return response()->json([
                                    'message' => 'Reference From is required',
                                    'error' => 'Reference found'
                                ], 500);
                            }
                        }
                        foreach ($request->param_ids as $orgServiceParamKey => $orgServiceParamId)
                        {
                            if (isset($referenceFrom) && $request->param_names[$orgServiceParamKey] === ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM)
                            {
                                $hasNonZeroValue = count(array_filter($referenceFrom, function ($value) {
                                    return $value != 0; })) > 0;
                                $val = isset($request->params[$orgServiceParamKey]) ? $request->params[$orgServiceParamKey] : [];
                                if ($hasNonZeroValue && count($val) == 0) {
                                    return response()->json([
                                        'message' => 'Reference Series is required',
                                        'error' => 'Reference found'
                                    ], 500);
                                }
                            }
                            if ($request->param_names[$orgServiceParamKey] === ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM)
                            {
                                $paramValues = isset($request->params[$orgServiceParamKey]) ? $request->params[$orgServiceParamKey] : [];
                                foreach ($paramValues as $paramValue) {
                                    $exists = OrganizationBookParameter::where('org_service_id', '=', $insert->org_service_id)->where('book_id', '!=', $insert -> id)->where('parameter_name', ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM)
                                    ->whereJsonContains('parameter_value', (string) $paramValue)->first();
                                    if ($exists) {
                                        $series = Book::find($paramValue);
                                        $seriesName = isset($series) ? $series->book_code : 'A Book Code';
                                        return response()->json([
                                            'message' => $seriesName . ' has already been referenced in '. $exists ?-> book -> book_code,
                                            'error' => 'Reference found'
                                        ], 500);
                                    }
                                }
                            }
                            if ($request->param_names[$orgServiceParamKey] === ServiceParametersHelper::SERVICE_ITEM_PARAM)
                            {

                                $paramValues = isset($request->params[$orgServiceParamKey]) ? $request->params[$orgServiceParamKey] : [];
                                foreach ($paramValues as $paramValue) {
                                    $exists = OrganizationBookParameter::where('org_service_id', '=', $insert->org_service_id)->where('book_id', '!=', $insert -> id)->where('parameter_name', ServiceParametersHelper::SERVICE_ITEM_PARAM)
                                    ->whereJsonContains('parameter_value', (string) $paramValue)->first();
                                }
                            }

                            OrganizationBookParameter::create([
                                'book_id' => $insert->id,
                                'group_id' => $organization->group_id,
                                'company_id' => $organization->company_id,
                                'organization_id' => $organization->id,
                                'org_service_id' => $request->org_service_id,
                                'service_param_id' => $orgServiceParamId,
                                'parameter_name' => $request->param_names[$orgServiceParamKey],
                                'parameter_value' => isset($request->params[$orgServiceParamKey]) ? $request->params[$orgServiceParamKey] : [],
                                'type' => ServiceParametersHelper::COMMON_PARAMETERS,
                                'status' => ConstantHelper::ACTIVE,
                            ]);
                        }
                    }
                }
                if (isset($request->gl_params) && isset($request->gl_param_names) && isset($request->gl_param_ids))
                {
                        foreach ($request->gl_param_ids as $orgServiceParamKey => $orgServiceParamId)
                        {
                            if ($request->gl_param_names[$orgServiceParamKey] === ServiceParametersHelper::GL_POSTING_SERIES_PARAM || $request->gl_param_names[$orgServiceParamKey] === ServiceParametersHelper::CONTRA_POSTING_SERIES_PARAM) {
                                $financialBookCode = isset($request->gl_params[$orgServiceParamKey]) ? $request->gl_params[$orgServiceParamKey][0] : null;
                                $financialBook = Book::withDefaultGroupCompanyOrg() -> where('book_code', $financialBookCode) -> where('manual_entry', 0) -> first();
                            }
                            else if ($request->gl_param_names[$orgServiceParamKey] === ServiceParametersHelper::CONTRA_POSTING_SERIES_PARAM) {
                                $financialBookCode = isset($request->gl_params[$orgServiceParamKey]) ? $request->gl_params[$orgServiceParamKey][0] : null;
                                $financialBook = Book::withDefaultGroupCompanyOrg() -> where('book_code', $financialBookCode) -> first();
                            }
                            OrganizationBookParameter::create([
                                'book_id' => $insert->id,
                                'group_id' => $organization->group_id,
                                'company_id' => $organization->company_id,
                                'organization_id' => $organization->id,
                                'org_service_id' => $request->org_service_id,
                                'service_param_id' => $orgServiceParamId,
                                'parameter_name' => $request->gl_param_names[$orgServiceParamKey],
                                'parameter_value' => isset($financialBook) ? [$financialBook -> id] : (isset($request->gl_params[$orgServiceParamKey]) ? $request->gl_params[$orgServiceParamKey] : []),
                                'type' => ServiceParametersHelper::GL_PARAMETERS,
                                'status' => ConstantHelper::ACTIVE,
                            ]);
                        }
                }
            }

            //Dynamic Fields
            if ($request -> dynamic_fields && count($request -> dynamic_fields) > 0) {
                $dynamicFieldIds = $request -> dynamic_fields;
                foreach ($dynamicFieldIds as $dynamicFieldId) {
                    BookDynamicField::create([
                        'book_id' => $insert -> id,
                        'dynamic_field_id' => $dynamicFieldId
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Book and Workflow created successfully!',
            ]);


        }

    catch(Exception $ex) {

            DB::rollBack();
            return response()->json([
                'message' => 'Some internal error occured!',
                'error' => $ex->getMessage() . $ex->getFile() . $ex->getLine()
            ], 500);
        }


    }

    public function edit_book($id)
    {
        $auth = Helper::getAuthenticatedUser();
        $userRole = $auth -> user_type;
        $book = Book::with([
            'common_parameters',
            'gl_parameters',
            'patterns',
            'levels' => function ($l) use($auth, $userRole) {
                $l -> when($userRole != ConstantHelper::IAM_SUPER_ADMIN, function ($subQuery) use($auth) {
                    $subQuery->where('organization_id', $auth -> organization_id) -> with(['approvers']);
                });
            },
            'amendments' => function ($l) use($auth, $userRole) {
                $l -> when($userRole != ConstantHelper::IAM_SUPER_ADMIN, function ($subQuery) use($auth) {
                    $subQuery->where('organization_id', $auth -> organization_id) -> with(['approvers']);
                });
            }
        ])->findOrFail($id);
        if (isset($book)) {
            //Patterns
            $serviceAlias = $book?->service?->alias;
            $serviceType = $book?->master_service?->type;
            if ($book->service->service?->type === ConstantHelper::ERP_TRANSACTION_SERVICE_TYPE) {
                foreach ($book->patterns as &$bookPattern) {
                    $modelName = isset(ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias]) ? ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias] : '';
                    if ($modelName)
                    {
                        $model = resolve('App\\Models\\' . $modelName);
                    $createdDocs = $model::where('organization_id', $bookPattern->organization_id)->where('book_id', $book->id)->whereNotNull('doc_no')->first();
                        if (isset($createdDocs)) {
                            $bookPattern->allow_change = false;
                            $bookPattern->allow_change_message = "Documents already created, Delete not allowed";
                        } else {
                            $bookPattern->allow_change = true;
                            $bookPattern->allow_change_message = "";
                        }
                    } else {
                        $bookPattern->allow_change = true;
                        $bookPattern->allow_change_message = "";
                    }
                }
            }
            foreach ($book -> levels as $approvalLevel => &$approver) {
                $levelEmployees = Helper::getOrgWiseUserAndEmployees($approver -> organization_id);
                $serviceAlias = $book ?-> service ?-> alias;
                $modelName = isset(ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias]) ? ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias] : '';
                if (isset($modelName)) {
                    $model = resolve('App\\Models\\' . $modelName);
                    $pendingDocs = $model::whereIn('document_status', [ConstantHelper::PARTIALLY_APPROVED, ConstantHelper::SUBMITTED])->where('approval_level', $approver->level)->get();
                    if (isset($pendingDocs) && count($pendingDocs) > 0) {
                        $approver->allow_change = false;
                        $approver->allow_change_message = "Pending Document Exists, Change is not allowed";
                    } else {
                        $approver->allow_change = true;
                        $approver->allow_change_message = "";
                    }
                } else {
                    $approver->allow_change = true;
                    $approver->allow_change_message = "";
                }
                $approver -> employees = $levelEmployees;
            }
            foreach ($book -> amendments as &$amendment) {
                $amendEmployees = Helper::getOrgWiseUserAndEmployees($amendment -> organization_id);
                $amendment -> employees = $amendEmployees;
            }

            foreach ($book->common_parameters as $bookParamKey => &$bookParam) {
                if ($bookParam->parameter_name === ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM) {
                    $orgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', $bookParam->parameter_name)->latest() -> first();
                    if (isset($orgServiceParam)) {
                        $selectOptions = "";
                        // $serviceIds = $orgServiceParam -> parameter_value;
                        $serviceIds = $orgServiceParam->service_parameter->applicable_values;
                        $services = Service::select('id', 'name', 'alias')->whereIn('id', $serviceIds)->get();
                        // $orgServiceIds = OrganizationService::withDefaultGroupCompanyOrg() -> whereIn('service_id', $serviceIds) -> get() -> pluck('id') -> toArray();
                        // $books = Book::withDefaultGroupCompanyOrg() -> whereIn('org_service_id', $orgServiceIds) -> get();
                        foreach ($services as $singleService) {
                            $label = strtoupper($singleService->alias);
                            $value = $singleService->id;
                            if (in_array($value, $bookParam->parameter_value)) {
                                $selectOptions .= "<option value = '$value' selected >$label</option>";
                            } else {
                                $selectOptions .= "<option value = '$value' >$label</option>";
                            }
                        }
                        if (in_array(0, $bookParam->parameter_value)) {
                            $selectOptions .= "<option value = '0' selected >Direct</option>";
                        } else if (in_array(0, $orgServiceParam->parameter_value)) {
                            $selectOptions .= "<option value = '0' >Direct</option>";
                        }
                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];

                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $htmlData = "
                        <div class='row align-items-center mb-1'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                <select
                                    class='form-select mw-100 select2 referenceService'
                                    multiple
                                    placeholder = 'Select Series'
                                    name = 'params[$bookParamKey][]'
                                    id = '$paramName'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        $bookParam->param_array_html = $htmlData;

                    }
                } else if ($bookParam->parameter_name === ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM) {
                    $orgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM)->latest()->first();
                    $actualOrgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', $bookParam->parameter_name)->first();
                    if (isset($orgServiceParam) && isset($actualOrgServiceParam)) {
                        $selectOptions = "";
                        $serviceIds = $orgServiceParam->service_parameter->applicable_values;
                        $services = Service::select('id', 'name', 'alias')->whereIn('id', $orgServiceParam->parameter_value)->get();
                        $books = ServiceParametersHelper::getAvailableReferenceSeries($bookParam?->org_service?->service_id, $serviceIds, $book->id);
                        foreach ($books as $singleBook) {
                            $label = ($singleBook->book_code);
                            $value = $singleBook->id;
                            if (in_array((string) $value, $bookParam->parameter_value)) {
                                $selectOptions .= "<option value = '$value' selected >$label</option>";
                            } else {
                                $selectOptions .= "<option value = '$value' >$label</option>";
                            }
                        }
                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$actualOrgServiceParam->parameter_name];

                        $paramName = $actualOrgServiceParam->parameter_name;
                        $paramId = $actualOrgServiceParam->service_param_id;
                        $htmlData = "
                        <div class='row align-items-center mb-1'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                <select
                                    id = 'reference_series_input'
                                    class='form-select mw-100 select2 bookSelect'
                                    multiple
                                    placeholder = 'Select Book'
                                    name = 'params[$bookParamKey][]'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        $bookParam->param_array_html = $htmlData;

                    }
                }else if ($bookParam->parameter_name === ServiceParametersHelper::SERVICE_ITEM_PARAM) {
                    $orgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', ServiceParametersHelper::SERVICE_ITEM_PARAM)->latest()->first();
                    $actualOrgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', $bookParam->parameter_name)->first();

                    if (isset($orgServiceParam) && isset($actualOrgServiceParam)) {
                          $selectOptions = "";
                        // $itemIds = $orgServiceParam -> parameter_value;
                        $itemIds = $orgServiceParam->service_parameter->applicable_values;
                        $items = Item::where('type', 'Service')->get();
                        foreach ($items as $item) {
                            $label = strtoupper($item->item_name);
                            $value = $item->id;

                            if (in_array($value, $bookParam->parameter_value)) {
                                $selectOptions .= "<option value = '$value' selected >$label</option>";
                            } else {
                                $selectOptions .= "<option value = '$value' >$label</option>";
                            }
                        }
                        if (in_array(0, $itemIds)) {
                            $selectOptions .= "<option value = '0' selected >Direct</option>";
                        }
                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$actualOrgServiceParam->parameter_name];

                        $paramName = $actualOrgServiceParam->parameter_name;
                        $paramId = $actualOrgServiceParam->service_param_id;
                        $htmlData = "
                        <div class='row align-items-center mb-1'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                <select
                                    id = 'service_item'
                                    class='form-select mw-100 select2 bookSelect'

                                    placeholder = 'Select Item'
                                    name = 'params[$bookParamKey][]'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        $bookParam->param_array_html = $htmlData;

                    }
                }else if ($bookParam->parameter_name === ServiceParametersHelper::OK_TO_RECIEVE_BOOK_PARAM) {
                    $orgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', ServiceParametersHelper::OK_TO_RECIEVE_BOOK_PARAM)->latest()->first();
                    $actualOrgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', $bookParam->parameter_name)->first();

                    if (isset($orgServiceParam) && isset($actualOrgServiceParam)) {
                        $selectOptions = "";
                        $bookIds = $orgServiceParam->service_parameter->applicable_values;
                        $orgService = ErpOrganizationService::where('alias', RepConstants::SERVICE_ALIAS) -> first();
                        $books = Book::select('id', 'book_code', 'book_name') -> where('org_service_id', $orgService ?-> id) 
                            -> where('status', ConstantHelper::ACTIVE) -> get();
                        foreach ($books as $book) {
                            $label = strtoupper($book->book_code);
                            $value = $book->id;

                            if (in_array($value, $bookParam->parameter_value)) {
                                $selectOptions .= "<option value = '$value' selected >$label</option>";
                            } else {
                                $selectOptions .= "<option value = '$value' >$label</option>";
                            }
                        }

                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$actualOrgServiceParam->parameter_name];

                        $paramName = $actualOrgServiceParam->parameter_name;
                        $paramId = $actualOrgServiceParam->service_param_id;
                        $htmlData = "
                        <div class='row align-items-center mb-1'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                <select
                                    id = 'service_item'
                                    class='form-select mw-100 select2 bookSelect'

                                    placeholder = 'Select Item'
                                    name = 'params[$bookParamKey][]'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        $bookParam->param_array_html = $htmlData;

                    }
                } else {
                    $multipleSelection = ($bookParam->parameter_name == ServiceParametersHelper::ISSUE_TYPE_PARAM) ? 'multiple' : '';
                    $multipleSelectionCommonClass = ($bookParam->parameter_name == ServiceParametersHelper::ISSUE_TYPE_PARAM) ? 'commonMultipleSelection' : '';
                    $label = ServiceParametersHelper::SERVICE_PARAMETERS[$bookParam->parameter_name];
                    $selectOptions = "";
                    $orgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', $bookParam->parameter_name)->latest()->first();
                    foreach ($orgServiceParam->service_parameter->applicable_values as $appValue) {
                        $optionLabel = ucfirst($appValue);
                        if (in_array($appValue, $bookParam->parameter_value)) {
                            $selectOptions .= "<option value = '$appValue' selected >$optionLabel</option>";
                        } else {
                            $selectOptions .= "<option value = '$appValue' >$optionLabel</option>";
                        }
                    }
                    $paramName = $bookParam->parameter_name;
                    $paramId = $bookParam->service_param_id;

                    $htmlData = "
                    <div class='row align-items-center mb-1'>
                        <div class='col-md-3'>
                            <label class='form-label'>$label</label>
                        </div>
                        <div class='col-md-5'>
                            <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                            <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                            <select
                                id = '$paramName'
                                class='form-select mw-100 select2 $multipleSelectionCommonClass'
                                data-id='1'
                                name = 'params[$bookParamKey][]'
                                $multipleSelection
                                >
                                $selectOptions
                            </select>
                        </div>
                    </div>
                    ";
                    $bookParam->param_array_html = $htmlData;
                }

            }
            foreach ($book->gl_parameters as $bookParamKey => &$bookParam) {
                if ($bookParam->parameter_name === ServiceParametersHelper::GL_POSTING_SERIES_PARAM) {
                    $orgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)
                        ->where('parameter_name', $bookParam->parameter_name)
                        ->latest()->first();
                    // dd($orgServiceParam);

                    if($orgServiceParam)
                    // if(isset($orgServiceParam->parameter_value) && count($orgServiceParam->parameter_value))
                    {
                        $selectOptions = "";

                        $financialServiceAlias = ServiceParametersHelper::getFinancialServiceAlias($serviceAlias);

                        $financialService = Service::where('alias', $financialServiceAlias)->first();

                        $applicableSeries = Book::withDefaultGroupCompanyOrg()
                            ->where('manual_entry', 0)
                            ->where('service_id', $financialService -> id)
                            ->get();

                        foreach ($applicableSeries as $singleSeries) {
                            $referencedBookIds = OrganizationBookParameter::where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)
                                -> whereJsonContains('parameter_value', $singleSeries -> id) -> where('book_id', "!=", $book -> id) -> first();
                            if (!isset($referencedBookIds)) {
                                $label = strtoupper($singleSeries->book_code);
                                $value = $singleSeries->id;
                                if (in_array($value, $bookParam->parameter_value)) {
                                    $selectOptions .= "<option value = '$value' selected >$label</option>";
                                } else {
                                    $selectOptions .= "<option value = '$value' >$label</option>";
                                }
                            }
                        }
                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];

                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $headerId = $paramName . "_header";
                        $htmlData = "
                        <div class='row align-items-center mb-1' id = '$headerId'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'gl_param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'gl_param_ids[]' />
                                <select
                                    class='form-select mw-100 select2 referenceService'
                                    placeholder = 'Select Series'
                                    name = 'gl_params[$bookParamKey][]'
                                    id = '$paramName'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        $bookParam->param_array_html = $htmlData;

                    }
                }
                else if ($bookParam->parameter_name === ServiceParametersHelper::CONTRA_POSTING_SERIES_PARAM) {
                    $orgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', $bookParam->parameter_name)->latest()->first();
                    if (isset($orgServiceParam)) {
                        $selectOptions = "";

                        $financialServiceAlias = ServiceParametersHelper::getFinancialServiceAlias($serviceAlias);

                        $financialService = Service::where('alias', $financialServiceAlias) -> first();

                        $applicableSeries = Helper::getContraBooks();
                        foreach ($applicableSeries as $singleSeries) {
                            $referencedBookIds = OrganizationBookParameter::where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)
                                -> whereJsonContains('parameter_value', $singleSeries -> id) -> where('book_id', "!=", $book -> id) -> first();
                            if (!isset($referencedBookIds)) {
                                $label = strtoupper($singleSeries->book_code);
                                $value = $singleSeries->id;
                                if (in_array($value, $bookParam->parameter_value)) {
                                    $selectOptions .= "<option value = '$value' selected >$label</option>";
                                } else {
                                    $selectOptions .= "<option value = '$value' >$label</option>";
                                }
                            }
                        }
                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];

                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $headerId = $paramName . "_header";
                        $htmlData = "
                        <div class='row align-items-center mb-1' id = '$headerId'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'gl_param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'gl_param_ids[]' />
                                <select
                                    class='form-select mw-100 select2 referenceService'
                                    placeholder = 'Select Series'
                                    name = 'gl_params[$bookParamKey][]'
                                    id = '$paramName'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        $bookParam->param_array_html = $htmlData;

                    }
                }
                else {
                    $label = ServiceParametersHelper::SERVICE_PARAMETERS[$bookParam->parameter_name];
                    $selectOptions = "";
                    $orgServiceParam = OrganizationServiceParameter::where('service_id', $book->org_service->service_id)->where('parameter_name', $bookParam->parameter_name)->latest()->first();
                    foreach ($orgServiceParam->service_parameter->applicable_values as $appValue) {
                        $optionLabel = ucfirst($appValue);
                        if (in_array($appValue, $bookParam->parameter_value)) {
                            $selectOptions .= "<option value = '$appValue' selected >$optionLabel</option>";
                        } else {
                            $selectOptions .= "<option value = '$appValue' >$optionLabel</option>";
                        }
                    }
                    $paramName = $bookParam->parameter_name;
                    $paramId = $bookParam->service_param_id;
                    $headerId = $paramName . "_header";
                    $onChange = $paramName === ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM ? "onchange = 'glPostingRequiredOnChange(this);'" : '';

                    $htmlData = "
                    <div class='row align-items-center mb-1' id = '$headerId'>
                        <div class='col-md-3'>
                            <label class='form-label'>$label</label>
                        </div>
                        <div class='col-md-5'>
                            <input type = 'hidden' value = '$paramName' name = 'gl_param_names[]' />
                            <input type = 'hidden' value = '$paramId' name = 'gl_param_ids[]' />
                            <select
                                id = '$paramName'
                                class='form-select mw-100 select2'
                                data-id='1'
                                name = 'gl_params[$bookParamKey][]'
                                $onChange
                                >
                                $selectOptions
                            </select>
                        </div>
                    </div>
                    ";
                    $bookParam->param_array_html = $htmlData;
                }

            }
            $modelName = isset(ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias]) ? ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias] : '';
            if (isset($modelName)) {
                $model = resolve('App\\Models\\' . $modelName);
                $createdDocs = $model::where('group_id', $book->group_id)->where('book_id', $book->id)->first();
                $referenced = OrganizationBookParameter::where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)
                            -> whereJsonContains('parameter_value', $book -> id) -> first();
                if (isset($createdDocs) || isset($referenced)) {
                    $book -> manual_entry_editable = false;
                    $book -> manual_entry_editable_message = "Change not allowed, Series is already utilized";
                } else {
                    $book -> manual_entry_editable = true;
                    $book -> manual_entry_editable_message = "";
                }
                if (isset($createdDocs) && !$book -> manual_entry) {
                    $book -> non_edit = true;
                }
            } else {
                $book -> manual_entry_editable = true;
                $book -> manual_entry_editable_message = "";
            }
        }
        $authUser = $auth;
        $userType = Helper::userCheck()['type'];
        //Get Org Access of logged in USER
        $authOrganization = Organization::find($authUser -> organization_id);
        $groupId = $authOrganization ?-> group_id;
        if ($authUser -> user_type === ConstantHelper::IAM_SUPER_ADMIN && $groupId) {
            $orgIds = Organization::where('group_id', $groupId) -> pluck('id') -> toArray();
        } else {
            $orgIds = $authUser -> organizations() -> pluck('organizations.id') -> toArray();
        }
        array_push($orgIds, $authUser?->organization_id);
       //Get Company according to Org
       $companyIds = Organization::whereIn('id', $orgIds) -> where('status', ConstantHelper::ACTIVE) -> get() -> pluck('company_id');
       $companies = OrganizationCompany::whereIn('id', $companyIds) -> with('organizations', function ($subQuery) use($orgIds) {
           $subQuery -> whereIn('id', $orgIds);
       }) -> get();
        $selectedDynamicFieldIds = $book -> dynamic_fields() -> pluck('dynamic_field_id') -> toArray();
        $people = collect([]);
        $dynamicFields = DynamicField::select('id', 'name') -> withDefaultGroupCompanyOrg() -> whereHas('items')
            -> where(function ($subQuery) use($selectedDynamicFieldIds) {
                $subQuery -> where('status', ConstantHelper::ACTIVE)
                -> orWhereIn('id', $selectedDynamicFieldIds);
            }) -> get();
        return view('book.edit-book', compact('book', 'companies', 'people', 'dynamicFields', 'selectedDynamicFieldIds', 'serviceType'));
    }

    public function update_book(Request $request, $id)
    {
        $request->validate([
            'book_name' => 'required|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
            // 'company_id' => 'required|array',
            // 'organization_id' => 'required|array',
            // 'series_numbering' => 'required|array',
            // 'reset_pattern' => 'array',
            'param_ids' => 'array',
            'param_names' => 'array',
            'params' => 'array'
        ]);

        try {
            DB::beginTransaction();
            // $organization = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->first();

            // Find and update the book
            $update = Book::find($id);
            $update->book_name = $request->book_name;
            $update->status = $request->status;
            // $update->group_id = $organization->group_id;
            // $update->company_id = $organization->company_id;
            // $update->organization_id = $organization->id;
            $update->manual_entry = $request->manual_entry === "yes" ? 1 : 0;
            $update->save();

            // Delete previous number patterns, approvalWorkflow and levels
            NumberPattern::where('book_id', $id)->delete();
            BookLevel::where('book_id', $id)->delete();
            ApprovalWorkflow::where('book_id', $id)->delete();
            AmendmentWorkflow::where('book_id', $id)->delete();
            AmendmentWorkflowUsers::where('book_id', $id)->delete();

            // Save new number patterns
            if ($request -> company_id) {
                foreach ($request->company_id as $key => $value) {
                    NumberPattern::create([
                        'book_id' => $update->id,
                        'company_id' => $request->company_id[$key],
                        'organization_id' => $request->organization_id[$key],
                        'series_numbering' => $request->series_numbering[$key],
                        'reset_pattern' => $request->reset_pattern[$key] ?? null,
                        'prefix' => $request->prefix[$key] ?? null,
                        'starting_no' => $request->starting_no[$key] ?? null,
                        'suffix' => $request->suffix[$key] ?? null,
                        'current_no' => $request->starting_no[$key] ?? 1,
                    ]);
                }
            }


            // Save approval workflows with individual users
            if ($request->level_company_id) {
                foreach ($request->level_company_id as $key => $level_company_id) {
                    $levelInsert = new BookLevel;
                    $levelInsert->book_id = $update->id;
                    $levelInsert->level = $request->level[$key];
                    //In case of Master type services -> this should always be 0
                    if ($update -> master_service ?-> type === ConstantHelper::ERP_MASTER_SERVICE_TYPE) {
                        $levelInsert->min_value = 0;
                    } else {
                        $levelInsert->min_value = $request->min_value[$key];
                    }
                    $rightsValue = $request->rights[$key] ?? null;

                    if (is_array($rightsValue)) {
                        $levelInsert->rights = json_encode($rightsValue); // for multi-select case
                    } else {
                        $levelInsert->rights = $rightsValue; // plain string like "anyone"
                    }
                    $levelInsert->company_id = $request->level_company_id[$key];
                    $levelInsert->organization_id = $request->level_organization_id[$key];
                    $levelInsert->save();

                    if (isset($request->user[$key])) {
                        foreach ($request->user[$key] as $user_info) {
                            ApprovalWorkflow::create([
                                'book_id' => $update->id,
                                'company_id' => $request->level_company_id[$key],
                                'book_level_id' => $levelInsert->id,
                                'organization_id' => $request->level_organization_id[$key],
                                'user_id' => $user_info,
                                'user_type' => 'employee'
                            ]);
                        }
                    }
                }
            }

            if ($request->amendment_company_id) {
                foreach ($request->amendment_company_id as $key => $amendment_company_id) {
                    // Insert levels
                    $amendmentInsert = new AmendmentWorkflow;
                    $amendmentInsert->book_id = $update->id;
                    //In case of Master type services -> min value should always be 0 and aaproval required false
                    if ($update -> master_service === ConstantHelper::ERP_MASTER_SERVICE_TYPE) {
                        $amendmentInsert->min_value = 0;
                        $amendmentInsert->approval_required = 0;
                    } else {
                        $amendmentInsert->min_value = $request->amendment_min[$key];
                        $amendmentInsert->approval_required = $request->approval_req[$key] == 'yes' ? 1 : 0;
                    }
                    $amendmentInsert->max_value = 0;
                    $amendmentInsert->company_id = $request->amendment_company_id[$key];
                    $amendmentInsert->organization_id = $request->amendment_organization_id[$key];
                    $amendmentInsert->save();

                    // Loop through each user info for that level
                    if (isset($request->amendment_user[$key])) {
                        foreach ($request->amendment_user[$key] as $user_info) {
                            AmendmentWorkflowUsers::create([
                                'book_id' => $update->id,
                                'company_id' => $request->amendment_company_id[$key],
                                'amendment_workflow_id' => $amendmentInsert->id,
                                'organization_id' => $request->amendment_organization_id[$key],
                                'user_id' => $user_info,
                                'user_type' => 'employee'
                            ]);
                        }
                    }
                }
            }

            if ($request->param_ids && $request->param_names) {
                foreach ($request->param_ids as $bookParamKey => $bookParamId) {
                    if ($request->param_names[$bookParamKey] === ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM) {
                        $paramValues = isset($request->params[$bookParamKey]) ? $request->params[$bookParamKey] : [];
                        foreach ($paramValues as $paramValue) {
                            $exists = OrganizationBookParameter::where('org_service_id', '=', $update->org_service_id)->where('book_id', '!=', $update -> id)->where('parameter_name', ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM)
                                ->whereJsonContains('parameter_value', (string) $paramValue)->first();
                            if ($exists) {
                                $series = Book::find($paramValue);
                                $seriesName = isset($series) ? $series->book_code : 'A Book Code';
                                return response()->json([
                                    'message' => $seriesName . ' has already been referenced in '. $exists ?-> book -> book_code,
                                    'error' => 'Reference found'
                                ], 500);
                            }
                        }
                    }

                    if ($request->param_names[$bookParamKey] === ServiceParametersHelper::SERVICE_ITEM_PARAM) {
                        $paramValues = isset($request->params[$bookParamKey]) ? $request->params[$bookParamKey] : [];
                        foreach ($paramValues as $paramValue) {
                            $exists = OrganizationBookParameter::where('org_service_id', '=', $update->org_service_id)->where('book_id', '!=', $update -> id)->where('parameter_name', ServiceParametersHelper::SERVICE_ITEM_PARAM)
                                ->whereJsonContains('parameter_value', (string) $paramValue)->first();

                            // if ($exists) {
                            //     $series = Book::find($paramValue);
                            //     $seriesName = isset($series) ? $series->book_code : 'A Book Code';
                            //     return response()->json([
                            //         'message' => $seriesName . ' has already been referenced in '. $exists ?-> book -> book_code,
                            //         'error' => 'Reference found'
                            //     ], 500);
                            // }
                        }
                    }
                    $existingBookParam = OrganizationBookParameter::where('book_id', $update->id)->where('service_param_id', $request->param_ids[$bookParamKey])->first();
                    if (isset($existingBookParam)) {
                        $existingBookParam->parameter_value = isset($request->params[$bookParamKey]) ? $request->params[$bookParamKey] : [];
                        $existingBookParam->save();
                    }
                }
            }
            if ($request->gl_params && $request->gl_param_ids && $request->gl_param_names) {
                foreach ($request->gl_param_ids as $bookParamKey => $bookParamId) {
                    $existingBookParam = OrganizationBookParameter::where('book_id', $update->id)->where('service_param_id', $request->gl_param_ids[$bookParamKey])->first();
                    if (isset($existingBookParam)) {
                        $existingBookParam->parameter_value = isset($request->gl_params[$bookParamKey]) ? $request->gl_params[$bookParamKey] : [];
                        $existingBookParam->save();
                    }
                }
            }
            //Dynamic Fields
            $newInsertedDynamicFieldIds = [];
            if ($request -> dynamic_fields && count($request -> dynamic_fields) > 0) {
                $dynamicFieldIds = $request -> dynamic_fields;
                foreach ($dynamicFieldIds as $dynamicFieldId) {
                    BookDynamicField::firstOrCreate([
                        'book_id' => $update -> id,
                        'dynamic_field_id' => $dynamicFieldId
                    ]);
                    array_push($newInsertedDynamicFieldIds, $dynamicFieldId);
                }
            }
            //Delete all non-linked previous records
            BookDynamicField::where('book_id', $update -> id) -> whereNotIn('dynamic_field_id', $newInsertedDynamicFieldIds) -> delete();
            DB::commit();
            return response()->json([
                'message' => __("message.updated", ['module' => "Series"]),
            ]);

        } catch(Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Some internal error occured!',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function destroy_book($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();
        return redirect("/book")->with('success', 'Book deleted successfully');
    }

    public function getServiceParamForBookCreation(Request $request, string $orgServiceId)
    {
        try {
            $commonParamsHTML = '';
            $glParamsHTML = '';
            $organizationService = OrganizationService::with('parameters')->find($orgServiceId);

            if (isset($organizationService)) {
                $masterService = $organizationService -> service;
                //Common Service
                foreach ($organizationService->common_parameters as $orgServiceParamKey => &$orgServiceParam) {
                    $currentParamHTML = '';

                    if ($orgServiceParam->parameter_name === ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM) {
                        $selectOptions = "";
                        // $serviceIds = $orgServiceParam -> parameter_value;
                        $serviceIds = $orgServiceParam->service_parameter->applicable_values;
                        $services = Service::whereIn('id', $serviceIds)->get();
                        foreach ($services as $service) {
                            $label = strtoupper($service->alias);
                            $value = $service->id;
                            if (in_array($value, $orgServiceParam->parameter_value)) {
                                $selectOptions .= "<option value = '$value' selected >$label</option>";
                            } else {
                                $selectOptions .= "<option value = '$value' >$label</option>";
                            }
                        }
                        if (in_array(0, $serviceIds)) {
                            $selectOptions .= "<option value = '0' selected >Direct</option>";
                        }
                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];

                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $htmlData = "
                        <div class='row align-items-center mb-1'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                <select
                                    class='form-select mw-100 select2 referenceService'
                                    multiple
                                    placeholder = 'Select Book'
                                    name = 'params[$orgServiceParamKey][]'
                                    id = '$paramName'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        // $orgServiceParam->param_array_html = $htmlData;
                        $currentParamHTML = $htmlData;

                    } else if ($orgServiceParam->parameter_name === ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM) {
                        $allParams = $organizationService->parameters;
                        $orgServiceParamService = $allParams->firstWhere('parameter_name', ServiceParametersHelper::REFERENCE_FROM_SERVICE_PARAM);
                        $selectOptions = "";
                        $serviceIds = isset($orgServiceParamService) ? $orgServiceParamService->parameter_value : [];
                        $books = ServiceParametersHelper::getAvailableReferenceSeries($orgServiceParam->service_id, $serviceIds);
                        foreach ($books as $singleBook) {
                            $label = $singleBook->book_code;
                            $value = $singleBook->id;
                            $selectOptions .= "<option value = '$value' >$label</option>";
                        }
                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];
                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $htmlData = "
                        <div class='row align-items-center mb-1'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                <select
                                    class='form-select mw-100 select2 bookSelect'
                                    id = 'reference_series_input'
                                    multiple
                                    placeholder = 'Select Book'
                                    name = 'params[$orgServiceParamKey][]'
                                    id = '$paramName'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        // $orgServiceParam->param_array_html = $htmlData;
                        $currentParamHTML = $htmlData;

                    }else if ($orgServiceParam->parameter_name === ServiceParametersHelper::SERVICE_ITEM_PARAM) {

                         $selectOptions = "";
                        // $itemIds = $orgServiceParam -> parameter_value;
                        $itemIds = $orgServiceParam->service_parameter->applicable_values;
                        $items = Item::where('type', 'Service')->get();
                        foreach ($items as $item) {
                            $label = strtoupper($item->item_name);
                            $value = $item->id;
                            if (in_array($value, $orgServiceParam->parameter_value)) {
                                $selectOptions .= "<option value = '$value' selected >$label</option>";
                            } else {
                                $selectOptions .= "<option value = '$value' >$label</option>";
                            }
                        }
                        if (in_array(0, $itemIds)) {
                            $selectOptions .= "<option value = '0' selected >Direct</option>";
                        }
                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];

                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $htmlData = "
                        <div class='row align-items-center mb-1'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                <select
                                    class='form-select mw-100 select2 referenceService'
                                    id='service_item'
                                    placeholder = 'Select Item'
                                    name = 'params[$orgServiceParamKey][]'
                                    id = '$paramName'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        // $orgServiceParam->param_array_html = $htmlData;
                        $currentParamHTML = $htmlData;

                    }else if ($orgServiceParam->parameter_name === ServiceParametersHelper::OK_TO_RECIEVE_BOOK_PARAM) {

                        $selectOptions = "";
                        $bookIds = $orgServiceParam->service_parameter->applicable_values;
                        $orgService = ErpOrganizationService::where('alias', RepConstants::SERVICE_ALIAS) -> first();
                        $books = Book::select('id', 'book_code', 'book_name') -> where('org_service_id', $orgService ?-> id) 
                            -> where('status', ConstantHelper::ACTIVE) -> get();
                        foreach ($books as $book) {
                            $label = strtoupper($book->book_code);
                            $value = $book->id;
                            if (in_array($value, $orgServiceParam->parameter_value)) {
                                $selectOptions .= "<option value = '$value' selected >$label</option>";
                            } else {
                                $selectOptions .= "<option value = '$value' >$label</option>";
                            }
                        }
                        $paramLabel = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];

                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $htmlData = "
                        <div class='row align-items-center mb-1'>
                            <div class='col-md-3'>
                                <label class='form-label'>$paramLabel</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                <select
                                    class='form-select mw-100 select2 referenceService'
                                    id='service_item'
                                    placeholder = 'Select Book'
                                    name = 'params[$orgServiceParamKey][]'
                                    id = '$paramName'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        // $orgServiceParam->param_array_html = $htmlData;
                        $currentParamHTML = $htmlData;

                    } else {
                        $label = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];
                        if (count(ServiceParametersHelper::SERVICE_PARAMETERS_VALUES[$orgServiceParam -> parameter_name]) == 0) { //Direct input type
                            $paramName = $orgServiceParam->parameter_name;
                            $paramId = $orgServiceParam->service_param_id;
                            $htmlData = "
                            <div class='row align-items-center mb-1'>
                                <div class='col-md-3'>
                                    <label class='form-label'>$label</label>
                                </div>
                                <div class='col-md-5'>
                                    <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                    <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                    <input
                                        type = 'text'
                                        class='form-select mw-100'
                                        id='$paramName'
                                        name = 'params[$orgServiceParamKey][]'>
                                    </input>
                                </div>
                            </div>
                            ";
                            $currentParamHTML = $htmlData;
                        } else {
                            $multipleSelection = ($orgServiceParam->parameter_name == ServiceParametersHelper::ISSUE_TYPE_PARAM) ? 'multiple' : '';
                            $multipleSelectionCommonClass = ($orgServiceParam->parameter_name == ServiceParametersHelper::ISSUE_TYPE_PARAM) ? 'commonMultipleSelection' : '';
                            $selectOptions = "";
                            foreach ($orgServiceParam->service_parameter->applicable_values as $appValue) {
                                $optionLabel = ucFirst($appValue);
                                if (in_array($appValue, $orgServiceParam->parameter_value)) {
                                    $selectOptions .= "<option value = '$appValue' selected >$optionLabel</option>";
                                } else {
                                    $selectOptions .= "<option value = '$appValue' >$optionLabel</option>";
                                }
                            }
                            $paramName = $orgServiceParam->parameter_name;
                            $paramId = $orgServiceParam->service_param_id;

                            $htmlData = "
                            <div class='row align-items-center mb-1'>
                                <div class='col-md-3'>
                                    <label class='form-label'>$label</label>
                                </div>
                                <div class='col-md-5'>
                                    <input type = 'hidden' value = '$paramName' name = 'param_names[]' />
                                    <input type = 'hidden' value = '$paramId' name = 'param_ids[]' />
                                    <select
                                        class='form-select mw-100 select2 $multipleSelectionCommonClass'
                                        id='$paramName'
                                        name = 'params[$orgServiceParamKey][]'
                                        $multipleSelection
                                        >
                                        $selectOptions
                                    </select>
                                </div>
                            </div>
                            ";
                            // $orgServiceParam->param_array_html = $htmlData;
                            $currentParamHTML = $htmlData;
                        }
                    }
                    $commonParamsHTML .= $currentParamHTML;
                }
                //GL Parameters
                foreach ($organizationService->gl_parameters as $orgServiceParamKey => &$orgServiceParam) {
                    $currentGlParam = '';
                    if ($orgServiceParam->parameter_name === ServiceParametersHelper::GL_POSTING_SERIES_PARAM) {
                        $label = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];
                        $selectOptions = "";
                        $financialServiceAlias = ServiceParametersHelper::getFinancialServiceAlias($organizationService -> service -> alias);
                        $financialService = Service::where('alias', $financialServiceAlias) -> first();

                        $applicableSeries = Book::withDefaultGroupCompanyOrg() -> where('manual_entry', 0) -> where('service_id', $financialService -> id) -> get();
                        $allBookCodes = [];
                        foreach ($applicableSeries as $book) {
                            $optionLabel = ($book -> book_code);
                            $selectOptions .= "<option value = '$optionLabel' >$optionLabel</option>";
                            array_push($allBookCodes, $book -> book_code);
                        }
                        if (!in_array($request -> book_code, $allBookCodes) && $request -> book_code)
                        {
                            $labelValue = $request -> book_code;
                            $selectOptions .= "<option value = '$labelValue' selected>$labelValue</option>";
                        }
                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $headerId = $paramName . "_header";

                        $htmlData = "
                        <div class='row align-items-center mb-1' id = '$headerId'>
                            <div class='col-md-3'>
                                <label class='form-label'>$label</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'gl_param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'gl_param_ids[]' />
                                <select
                                    class='form-select mw-100 select2'
                                    id='$paramName'
                                    name = 'gl_params[$orgServiceParamKey][]'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        $currentGlParam = $htmlData;
                    }else if ($orgServiceParam->parameter_name === ServiceParametersHelper::CONTRA_POSTING_SERIES_PARAM) {
                        $label = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];
                        $selectOptions = "";
                        $financialServiceAlias = ServiceParametersHelper::getFinancialServiceAlias($organizationService -> service -> alias);
                        $financialService = Service::where('alias', $financialServiceAlias) -> first();

                        $applicableSeries = Helper::getContraBooks();
                        $allBookCodes = [];

                        foreach ($applicableSeries as $book) {
                            $optionLabel = ($book -> book_code);
                            $selectOptions .= "<option value = '$optionLabel' >$optionLabel</option>";
                            array_push($allBookCodes, $book -> book_code);
                        }
                        if (!in_array($request -> book_code, $allBookCodes) && $request -> book_code)
                        {
                            $labelValue = $request -> book_code;
                            $selectOptions .= "<option value = '$labelValue' selected>$labelValue</option>";
                        }
                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $headerId = $paramName . "_header";

                        $htmlData = "
                        <div class='row align-items-center mb-1' id = '$headerId'>
                            <div class='col-md-3'>
                                <label class='form-label'>$label</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'gl_param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'gl_param_ids[]' />
                                <select
                                    class='form-select mw-100 select2'
                                    id='$paramName'
                                    name = 'gl_params[$orgServiceParamKey][]'
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        $currentGlParam = $htmlData;
                    } else {
                        $label = ServiceParametersHelper::SERVICE_PARAMETERS[$orgServiceParam->parameter_name];
                        $selectOptions = "";
                        foreach ($orgServiceParam->service_parameter->applicable_values as $appValue) {
                            $optionLabel = ucFirst($appValue);
                            if (in_array($appValue, $orgServiceParam->parameter_value)) {
                                $selectOptions .= "<option value = '$appValue' selected >$optionLabel</option>";
                            } else {
                                $selectOptions .= "<option value = '$appValue' >$optionLabel</option>";
                            }
                        }
                        $paramName = $orgServiceParam->parameter_name;
                        $paramId = $orgServiceParam->service_param_id;
                        $headerId = $paramName . "_header";
                        $onChange = $paramName === ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM ? "onchange = 'glPostingRequiredOnChange(this);'" : '';

                        $htmlData = "
                        <div class='row align-items-center mb-1' id = '$headerId'>
                            <div class='col-md-3'>
                                <label class='form-label'>$label</label>
                            </div>
                            <div class='col-md-5'>
                                <input type = 'hidden' value = '$paramName' name = 'gl_param_names[]' />
                                <input type = 'hidden' value = '$paramId' name = 'gl_param_ids[]' />
                                <select
                                    class='form-select mw-100 select2'
                                    id='$paramName'
                                    name = 'gl_params[$orgServiceParamKey][]'
                                    $onChange
                                    >
                                    $selectOptions
                                </select>
                            </div>
                        </div>
                        ";
                        $currentGlParam = $htmlData;
                    }

                    $glParamsHTML .= $currentGlParam;
                }
                return response()->json([
                    'status' => 'success',
                    'data' => array(
                        'common_parameters' => $commonParamsHTML,
                        'gl_parameters' => $glParamsHTML,
                        'service_type' => $masterService -> type
                    )
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No Service found'
                ], 422);
            }
        } catch (Exception $ex) {
            return response()->json([
                'status' => 'exception',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function getBookDocNoAndParameters(Request $request)
    {
        try {
            $book = Book::find($request->book_id);
            if (isset($book)) {
                $parameters = new stdClass();
                foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                    $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                    if(count($param)) {
                        $parameters->{$paramName} = $param;
                    }
                }
                $docNum = Helper::generateDocumentNumberNew($book->id, $request->document_date, $parameters);

                if (isset($docNum['error'])) {
                    return response()->json(['data' => [], 'message' => $docNum['error'], 'status' => 500]);
                }

                $lotNumber = InventoryHelper::generateLotNumber($request->document_date, $book->book_code, $docNum['document_number'] ?? "");
                $selectedDynamicFields = $book -> dynamic_fields() -> pluck('dynamic_field_id') -> toArray();
                $dynamicFields = DynamicFieldDetail::select('id', 'header_id', 'name', 'data_type') -> whereIn('header_id', $selectedDynamicFields) -> whereHas('header') -> get();
                $dynamicFieldsHTML = "";
                foreach ($dynamicFields as $dynamicField) {
                    $dynamicFieldsHTML .= DynamicFieldHelper::generateFieldUI($dynamicField);
                }
                $dynamicFieldsBaseHTML = "
                        <div class='card quation-card'>
                            <div class='card-header newheader'>
                                <div>
                                    <h4 class='card-title'>Dynamic Fields</h4>
                                </div>
                            </div>
                            <div class='card-body'>
                                <div class='row'>
                                    $dynamicFieldsHTML
                                </div>
                            </div>
                        </div>
                    ";
                return response()->json([
                    'data' => [
                        'doc' => $docNum,
                        'lot_number' => $lotNumber,
                        'book_code' => $book->book_code,
                        'parameters' => $parameters,
                        'dynamic_fields' => $dynamicFields,
                        'dynamic_fields_html' => $dynamicFieldsBaseHTML
                    ],
                    'message' => "fetched!",
                    'status' => 200
                ]);

            } else {
                return response()->json(['data' => [], 'message' => "No record found!", 'status' => 404]);
            }
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $ex->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function firstOrNewBookDocNoAndParameters(Request $request)
    {
        try {
            $book = Book::find($request->book_id);
            if (isset($book)) {
                $parameters = new stdClass();
                foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                    $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                    if(count($param)) {
                        $parameters->{$paramName} = $param;
                    }
                }

                $docNum = Helper::firstOrNewDocumentNumber($book->id, $request->document_date, $request->document_number, $parameters);
                if (isset($docNum['error'])) {
                    return response()->json(['data' => [], 'message' => $docNum['error'], 'status' => 500]);
                }

                $lotNumber = InventoryHelper::generateLotNumber($request->document_date, $book->book_code, $docNum['document_number'] ?? "");
                $selectedDynamicFields = $book -> dynamic_fields() -> pluck('dynamic_field_id') -> toArray();
                $dynamicFields = DynamicFieldDetail::select('id', 'header_id', 'name', 'data_type') -> whereIn('header_id', $selectedDynamicFields) -> whereHas('header') -> get();
                $dynamicFieldsHTML = "";
                foreach ($dynamicFields as $dynamicField) {
                    $dynamicFieldsHTML .= DynamicFieldHelper::generateFieldUI($dynamicField);
                }
                $dynamicFieldsBaseHTML = "
                        <div class='card quation-card'>
                            <div class='card-header newheader'>
                                <div>
                                    <h4 class='card-title'>Dynamic Fields</h4>
                                </div>
                            </div>
                            <div class='card-body'>
                                <div class='row'>
                                    $dynamicFieldsHTML
                                </div>
                            </div>
                        </div>
                    ";
                return response()->json([
                    'data' => [
                        'doc' => $docNum,
                        'lot_number' => $lotNumber,
                        'book_code' => $book->book_code,
                        'parameters' => $parameters,
                        'dynamic_fields' => $dynamicFields,
                        'dynamic_fields_html' => $dynamicFieldsBaseHTML
                    ],
                    'message' => "fetched!",
                    'status' => 200
                ]);

            } else {
                return response()->json(['data' => [], 'message' => "No record found!", 'status' => 404]);
            }
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $ex->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function checkLevelForChange(Request $request)
    {
        try {
            $book = Book::find($request->book_id);
            if (!isset($book)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Book not found',
                ], 404);
            }
            $serviceAlias = $book?->service?->alias;
            $modelName = isset(ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias]) ? ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias] : '';
            if (!isset($modelName)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Service not found',
                ], 404);
            }
            $model = resolve('App\\Models\\' . $modelName);
            $pendingDocs = $model::whereIn('document_status', [ConstantHelper::PARTIALLY_APPROVED, ConstantHelper::SUBMITTED])->where('approval_level', $request->approval_level)->get();
            $allowChange = true;
            if (isset($pendingDocs) && $pendingDocs->count() > 0) {
                $allowChange = false;
            }
            return response()->json([
                'status' => 'success',
                'allow_change' => $allowChange
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function getEmployeesForApprovalOrgWise(Request $request)
    {
        try {
            $employees = Helper::getOrgWiseUserAndEmployees($request -> organization_id);
            return response()->json([
                'status' => 'success',
                'data' => $employees
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'status' => 'exception',
                'message' => $ex -> getMessage()
            ], 500);
        }
    }

    public function getReferenceSeriesFromReferenceService(Request $request)
    {
        try {
            $serviceIds = isset($request->service_ids) && is_array($request->service_ids) ? $request->service_ids : [];
            $bookId = isset($request->book_id) ? $request->book_id : 0;
            $orgService = OrganizationService::find($request->service_id);
            $books = ServiceParametersHelper::getAvailableReferenceSeries($orgService?->service_id ?? 0, $serviceIds, $bookId);
            $bookValues = [];
            $previousSelectedBooks = isset($request->selected_ids) && is_array($request->selected_ids) ? $request->selected_ids : [];
            foreach ($books as $singleBook) {
                array_push($bookValues, [
                    'label' => $singleBook->book_code,
                    'value' => $singleBook->id,
                    'selected' => in_array($singleBook->id, $previousSelectedBooks) ? true : false,
                    'disabled' => in_array($singleBook->id, $previousSelectedBooks) ? true : false
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Books found',
                'data' => $bookValues
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'status' => 'exception',
                'error' => $ex->getMessage(),
                'message' => 'Some internal error occured'
            ], 500);
        }
    }

    public function getSeriesOfService(Request $request)
    {
        try {
            $serviceAlias = $request -> service_alias ?? null;
            $menuAlias = $request -> menu_alias ?? null;
            $onlyBookId = $request -> book_id ?? null;
            $books = Helper::getBookSeriesNew($serviceAlias, $menuAlias, $onlyBookId ? true : false);
            if ($onlyBookId) {
                $books = $books -> where('id', $onlyBookId) -> get();
            } else {
                $books = $books -> get();
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Books found',
                'data' => $books
            ]);
        } catch(Exception $ex) {
            return response()->json([
                'status' => 'exception',
                'error' => $ex->getMessage(),
                'message' => 'Some internal error occured'
            ], 500);
        }

    }
}
