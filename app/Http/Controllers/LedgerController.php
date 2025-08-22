<?php

namespace App\Http\Controllers\Ledger;

use App\Exports\FailedLedgersExport;
use App\Exports\LedgerExport;
use App\Exports\LedgersExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\CostCenter;
use App\Models\Group;
use Illuminate\Support\Facades\DB;
use Exception;


use App\Models\Ledger;
use App\Models\Organization;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Helpers\ConstantHelper;
use App\Imports\LedgerImport;
use App\Mail\ImportComplete;
use App\Models\Voucher;
use App\Models\ItemDetail;
use App\Models\LedgerHistory;
use App\Models\PaymentVoucherDetails;
use App\Models\UploadLedgerMaster;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\LedgerImportExportService;
use Illuminate\Support\Facades\Mail;
use App\Helpers\ServiceParametersHelper;
use stdClass;

class LedgerController extends Controller
{
    protected $ledgerImportExportService;

    public function __construct(LedgerImportExportService $ledgerImportExportService)
    {
        $this->ledgerImportExportService = $ledgerImportExportService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $organizationId = $user->organization_id;

            if ($request->ajax()) {
                $organizations = null;


                if ($request->filter_organization && is_array($request->filter_organization)) {
                    // Loop through the filter_organization array and push each value to $organizations
                    foreach ($request->filter_organization as $value) {
                        $organizations[] = $value;  // Push each value to $organizations
                    }
                }
                // if (count($organizations) == 0) {
                //     $organizations[] = $organizationId;
                // }

                // $ledgers = Ledger::whereIn('organization_id', $organizations)->orderBy('id', 'desc');
                // $ledgers = Ledger::orderBy('id', 'desc')->get();
                // if ($request->group) {
                //     $ledgers->whereJsonContains('ledger_group_id', (string) $request->group)
                //         ->orWhere('ledger_group_id', $request->group);
                // }
                // if ($request->status) {
                //     $ledgers->where('status', $request->status == "Active" ? 1 : 0);
                // }
                // if ($request->date) {
                //     $dates = explode(' to ', $request->date);
                //     $start = date('Y-m-d', strtotime($dates[0]));
                //     $end = date('Y-m-d', strtotime($dates[1]));
                //     $ledgers->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
                // }
                $ledgersQuery = Ledger::when(!empty($organizations), function ($query) use ($organizations) {
                    $query->whereIn('organization_id', $organizations);
                })
                    ->orderBy('id', 'desc');

                if ($request->group) {
                    $ledgersQuery->whereJsonContains('ledger_group_id', (string) $request->group)
                        ->orWhere('ledger_group_id', $request->group);
                }

                if ($request->status) {
                    $ledgersQuery->where('status', $request->status == "Active" ? 1 : 0);
                }

                if ($request->date) {
                    $dates = explode(' to ', $request->date);
                    $start = date('Y-m-d', strtotime($dates[0]));
                    $end = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $start;
                    $ledgersQuery->whereDate('created_at', '>=', $start)
                        ->whereDate('created_at', '<=', $end);
                }
                if ($request->ledger) {
                    $searchTerm = $request->input('ledger');
                    $ledgersQuery->where(function ($query) use ($searchTerm) {
                        $query->where('id', $searchTerm);
                    });
                }
                $ledgers = $ledgersQuery->get();
                return DataTables::of($ledgers)
                    ->addColumn('group_name', function ($ledger) {
                        $groups = $ledger->group();
                        if ($groups && $groups instanceof \Illuminate\Database\Eloquent\Collection) {
                            $groupNames = $groups->pluck('name')->implode(', ');
                        } else if ($groups) {
                            $groupNames = $groups->name ?? "-";
                        } else {
                            $groupNames = '';
                        }
                        return $groupNames;
                    })->addColumn('costCenter', function ($ledger) {
                        return $ledger->costCenter ? $ledger->costCenter->name : 'N/A';
                    })
                    ->addColumn('status', function ($ledger) {
                        $status = in_array($ledger->document_status, [ConstantHelper::REJECTED, ConstantHelper::SUBMITTED, ConstantHelper::PARTIALLY_APPROVED]) ?
                            $ledger->document_status : 'inactive';
                        if ($ledger->document_status != null) {
                            if ($ledger->status == 1) {
                                $btn = '<span class="badge rounded-pill badge-light-success badgeborder-radius">Active</span>';
                            } else {
                                $statusClass = ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$status ?? "draft"];
                                $btn = '<span class="badge rounded-pill ' . $statusClass . ' badgeborder-radius">' . ucfirst($status) . '</span>';
                            }
                        } else {
                            if ($ledger->status == 1) {
                                $btn = '<span class="badge rounded-pill badge-light-success badgeborder-radius">Active</span>';
                            } else {
                                $btn = '<span class="badge rounded-pill badge-light-danger badgeborder-radius">InActive</span>';
                            }
                        }


                        return $btn;
                    })
                    ->editColumn('created_at', function ($data) {
                        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y');
                        return $formatedDate;
                    })
                    ->addColumn('action', function ($ledger) {
                        return '
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . route('ledgers.edit', ['ledger' => $ledger->id]) . '">
                                <i data-feather="edit-3" class="me-50"></i>
                                <span>View</span>
                            </a>

                            <!--<a class="delete-btn dropdown-item"
                                    data-url="' . route('ledgers.destroy', ['ledger' => $ledger->id]) . '"
                                    data-redirect="' . route('ledgers.index') . '"
                                    data-message="Are you sure you want to delete this ledger?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </a>-->
                        </div>
                    </div>';
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            $groups = Helper::getGroupsQuery()->whereNotNull('parent_group_id')->select('id', 'name')->get();
            $ledgers = Ledger::select('id', 'name')->orderBy('id', 'desc')->get();
            $user = Helper::getAuthenticatedUser();
       
            $mappings = Helper::access_org();

            return view('ledgers.view_ledgers', compact('groups', 'ledgers', 'mappings', "organizationId"));
        } catch (\Exception $e) {
            Log::error('Error fetching ledgers: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $costCenters = CostCenter::where('status', 'active')->where('organization_id', Helper::getAuthenticatedUser()->organization_id)->get();
        $parentGroupIds = Helper::getGroupsQuery()->whereNotNull('parent_group_id')
            ->pluck('parent_group_id')
            ->unique();

        $orgId = Helper::getAuthenticatedUser()->organization_id;

        $groups = Helper::getGroupsQuery()
            ->select('id', 'name', 'parent_group_id')
            ->get()
            ->reject(function ($g) use ($parentGroupIds, $orgId) {
                // Check if the group is in parentGroupIds and if it belongs to the same org or is null
                return $parentGroupIds->contains($g->id) &&
                    ($g->organization_id === $orgId || $g->organization_id === null);
            });

        $group_name = "GST";
        $tds_group_name = "TDS";
        $tcs_group_name = "TCS";
        $gst_group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $tds_group = Helper::getGroupsQuery()->where('name', $tds_group_name)->first();
        $tcs_group = Helper::getGroupsQuery()->where('name', $tcs_group_name)->first();
        if (isset($gst_group->id))
            $gst_group_id = $gst_group->id;
        else
            $gst_group_id = "null";
        if (isset($gst_group->id))
            $tds_group_id = $tds_group->id;
        else
            $tds_group_id = "null";
        if (isset($tcs_group->id))
            $tcs_group_id = $tcs_group->id;
        else
            $tcs_group_id = "null";
        $taxTypes = ConstantHelper::getTaxTypes();
        $tdsSections = ConstantHelper::getTdsSections();
        $tcsSections = ConstantHelper::getTcsSections();
        //        $label = ConstantHelper::getTaxTypeLabel(ConstantHelper::TAX_TYPE_IGST);
        $Existingledgers = Ledger::select('name', 'code')->get();

        // Get existing TDS sections grouped by ledger groups for frontend validation
        $ExistingTdsSections = Ledger::select('ledger_group_id', 'tds_section')
            ->whereNotNull('tds_section')
            ->where('tds_section', '!=', '')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($ledger) {
                $groupIds = json_decode($ledger->ledger_group_id, true) ?? [];
                return [
                    'ledger_group_ids' => $groupIds,
                    'tds_section' => $ledger->tds_section
                ];
        });

        $parentUrl = ConstantHelper::LEDGERS_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $itemCodeType = 'Manual';
        $book_id = null;
        if ($services && $services['current_book']) {
            if (isset($services['current_book'])) {
                $book_id = $services['current_book']->id;
                $book = $services['current_book'];
                if ($book) {
                    $parameters = new stdClass();
                    foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                        $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                        $parameters->{$paramName} = $param;
                    }
                    if (isset($parameters->ledger_code_type) && is_array($parameters->ledger_code_type)) {
                        $itemCodeType = $parameters->ledger_code_type[0] ?? null;
                    }
                }
            }

        }


        return view('ledgers.add_ledger', compact('itemCodeType', 'book_id', 'costCenters', 'groups', 'gst_group_id', 'tds_group_id', 'tcs_group_id', 'taxTypes', 'tdsSections', 'tcsSections', 'Existingledgers', 'ExistingTdsSections'));
    }

    public function showImportForm()
    {
        $parentUrl = ConstantHelper::LEDGERS_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $itemCodeType = 'Manual';
        if ($services && $services['current_book']) {
            if (isset($services['current_book'])) {
                $book = $services['current_book'];
                if ($book) {
                    $parameters = new stdClass();
                    foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                        $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                        $parameters->{$paramName} = $param;
                    }
                    if (isset($parameters->ledger_code_type) && is_array($parameters->ledger_code_type)) {
                        $itemCodeType = $parameters->ledger_code_type[0] ?? null;
                    }
                }
            }
        }
        return view('ledgers.import', compact('itemCodeType'));
    }

    public function import(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:30720',
            ]);
            if (!$request->hasFile('file')) {
                return response()->json([
                    'status' => false,
                    'message' => 'No file uploaded.',
                ], 400);
            }
            $file = $request->file('file');
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(filename: $file);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file format is incorrect or corrupted. Please upload a valid Excel file.',
                ], 400);
            }

            $sheet = $spreadsheet->getActiveSheet();
            $rowCount = $sheet->getHighestRow() - 1;
            if ($rowCount > 10000) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file contains more than 10000 items. Please upload a file with 10000 or fewer items.',
                ], 400);
            }
            if ($rowCount < 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file is empty.',
                ], 400);
            }
            $deleteQuery = UploadLedgerMaster::where('user_id', $user->id);
            $deleteQuery->delete();
            $parentUrl = ConstantHelper::LEDGERS_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            $itemCodeType = 'Manual';
            $book_id = null;
            if ($services && $services['current_book']) {
                if (isset($services['current_book'])) {
                    $book = $services['current_book'];
                    $book_id = $services['current_book']->id;

                    if ($book) {
                        $parameters = new stdClass();
                        foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                            $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                            $parameters->{$paramName} = $param;
                        }
                        if (isset($parameters->ledger_code_type) && is_array($parameters->ledger_code_type)) {
                            $itemCodeType = $parameters->ledger_code_type[0] ?? null;
                        }
                    }
                }
            }

            $import = new LedgerImport($this->ledgerImportExportService, $user, $itemCodeType, $book_id);
            Excel::import($import, $request->file('file'));

            $successfulItems = $import->getSuccessfulItems();
            $failedItems = $import->getFailedItems();
            $mailData = [
                'modelName' => 'Ledgers',
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
                'export_successful_url' => route('ledgers.export.successful'),
                'export_failed_url' => route('ledgers.export.failed'),
            ];
            if (count($failedItems) > 0) {
                $message = 'Items import failed.';
                $status = 'failure';
            } else {
                $message = 'Items imported successfully.';
                $status = 'success';
            }
            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new ImportComplete($mailData));
                } catch (\Exception $e) {
                    $message .= " However, there was an error sending the email notification.";
                }
            }
            return response()->json([
                'status' => $status,
                'message' => $message,
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid file format or file size. Please upload a valid .xlsx or .xls file with a maximum size of 30MB.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to import items: ',
            ], 500);
        }
    }

    public function exportSuccessfulItems()
    {
        $uploadItems = UploadLedgerMaster::where('import_status', 'Success')
            ->get();
        $items = Ledger::orderBy('id', 'desc')
            ->whereIn('code', $uploadItems->pluck('code'))->get();
        return Excel::download(new LedgersExport($items, $this->ledgerImportExportService), "successful-items.xlsx");
    }

    public function exportFailedItems()
    {
        $failedItems = UploadLedgerMaster::where('import_status', 'Failed')
            ->get();
        return Excel::download(new FailedLedgersExport($failedItems), "failed-items.xlsx");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $authOrganization = Helper::getAuthenticatedUser()->organization;
        $organizationId = $authOrganization->id;
        $companyId = $authOrganization?->company_id;
        $groupId = $authOrganization?->group_id;

        // Validate the request data
        $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('erp_ledgers', 'code')->where(function ($query) use ($organizationId, $companyId, $groupId) {
                    return $query->where('organization_id', $organizationId)
                        ->where('company_id', $companyId)
                        ->where('group_id', $groupId);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('erp_ledgers', 'name')->where(function ($query) use ($organizationId, $companyId, $groupId) {
                    return $query->where('organization_id', $organizationId)
                        ->where('company_id', $companyId)
                        ->where('group_id', $groupId);
                }),
            ],
            'tax_type' => [
                'nullable',
                'string',
                'max:255',
            ],
            'tax_percentage' => [
                'nullable',
                'numeric',
                'max:255',
            ],
            'tds_section' => [
                'nullable',
                'string',
                'max:255',
            ],
            'tds_percentage' => [
                'nullable',
                'numeric',
                'max:255',
            ],
            'tds_without_pan' => [
                'nullable',
                'numeric',
                'max:255',
            ],
            'tcs_section' => [
                'nullable',
                'string',
                'max:255',
            ],
            'tcs_capping' => [
                'nullable',
                'numeric',
            ],
            'tds_capping' => [
                'nullable',
                'numeric',
            ],
            'tcs_percentage' => [
                'nullable',
                'numeric',
                'max:255',
            ],
            'ledger_code_type' => [
                'nullable',
                'string',
                'max:255',
            ],
            'prefix' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);
        $user = Helper::getAuthenticatedUser();
        $request->merge([
            'created_by' => $user->auth_user_id,
        ]);
        $existingName = Ledger::where('code', $request->name)
            ->first();

        $existingCode = Ledger::where('code', $request->code)
            ->first();


        if ($existingName) {
            return back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
        }

        if ($existingCode) {
            return back()->withErrors(['code' => 'The code has already been taken.'])->withInput();
        }
        $request->merge([
            'ledger_group_id' => isset($request->ledger_group_id) ? json_encode($request->ledger_group_id) : null,
        ]);

        $ledgerGroupIds = $request->ledger_group_id ?? [];
        $groupNames = Helper::getGroupsQuery()->whereIn('id', (array) json_decode($ledgerGroupIds))
            ->pluck('name')
            ->map(function ($name) {
                return strtolower(trim($name));
            })
            ->toArray();


        // Clean out unnecessary fields
        if (!in_array('tds', $groupNames)) {
            $request->request->remove('tds_section');
            $request->request->remove('tds_percentage');
            $request->request->remove('tds_capping');
        }

        if (!in_array('tcs', $groupNames)) {
            $request->request->remove('tcs_section');
            $request->request->remove('tcs_percentage');
            $request->request->remove('tcs_capping');
        }

        if (!in_array('gst', $groupNames)) {
            $request->request->remove('tax_type');
            $request->request->remove('tax_percentage');
        }

        $parentUrl = ConstantHelper::LEDGERS_SERVICE_ALIAS;
        $validatedData = Helper::prepareValidatedDataWithPolicy($parentUrl);
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if ($services && $services['current_book']) {
            if (isset($services['current_book'])) {
                $book = $services['current_book'];
                if ($book) {
                    $parameters = new stdClass();
                    foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                        $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                        $parameters->{$paramName} = $param;
                    }
                    if (isset($parameters->ledger_code_type) && is_array($parameters->ledger_code_type)) {
                        $itemCodeType = $parameters->ledger_code_type[0] ?? null;
                    }
                }
            }
        }


        // Create a new ledger record with organization details
        $ledger = Ledger::create(array_merge($request->all(), $validatedData));
        if ($request->has('prefix') && $request->prefix != "")
            Group::updatePrefix($ledger->id, $request->prefix);
        $bookId = $ledger->book_id;
        $docId = $ledger->id;
        $remarks = $request->remarks;
        $attachments = $request->file('attachment');
        $currentLevel = $item->approval_level ?? 1;
        $revisionNumber = $item->revision_number ?? 0;
        $actionType = 'submit';
        $modelName = get_class($ledger);
        $totalValue = 0;

        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
        $document_status = $approveDocument['approvalStatus'];
        $ledger->document_status = $document_status;
        if (!in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
            $ledger->status = 0;
        } else {
            $ledger->status = 1;
        }
        $ledger->save();




        return redirect()->route('ledgers.index')->with('success', 'Ledger created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $rev = Ledger::find($id)->revision_number;
        $user = Helper::getAuthenticatedUser();
        if ($request->has('revisionNumber') && ((int) $request->revisionNumber != Ledger::find($id)->revision_number))
            $data = LedgerHistory::where('source_id', $id)->where('revision_number', $request->revisionNumber)->firstorFail();
        else
            $data = Ledger::find($id);


        $costCenters = CostCenter::where('status', 'active')
            ->where('organization_id', $user->organization_id);

        if ($data->cost_center_id) {
            $costCenters->orWhere('id', $data->cost_center_id);
        }
        $costCenters = $costCenters->get();

        $parentGroupIds = Helper::getGroupsQuery()->whereNotNull('parent_group_id')
            ->pluck('parent_group_id')
            ->unique();

        $orgId = Helper::getAuthenticatedUser()->organization_id;

        $groups = Helper::getGroupsQuery()->select('id', 'name', 'parent_group_id')
            ->get()
            ->reject(function ($g) use ($parentGroupIds, $orgId) {
                // Check if the group is in parentGroupIds and if it belongs to the same org or is null
                return $parentGroupIds->contains($g->id) &&
                    ($g->organization_id === $orgId || $g->organization_id === null);
            });

        $groupsModal = $data->group();

        // Get special groups
        $group_name = "GST";
        $tds_group_name = "TDS";
        $tcs_group_name = "TCS";
        $gst_group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $tds_group = Helper::getGroupsQuery()->where('name', $tds_group_name)->first();
        $tcs_group = Helper::getGroupsQuery()->where('name', $tcs_group_name)->first();

        $gst_group_id = $gst_group->id ?? "null";
        $tds_group_id = $tds_group->id ?? "null";
        $tcs_group_id = $tcs_group->id ?? "null";

        // Handle ledger_group_id format
        if (is_int($data->ledger_group_id)) {
            $data->ledger_group_id = json_encode([$data->ledger_group_id]);
        } elseif (!is_array($decoded = json_decode($data->ledger_group_id, true))) {
            $data->ledger_group_id = json_encode([$data->ledger_group_id]);
        } else {
            $data->ledger_group_id = json_encode($decoded);
        }
        $existingLedgers = Ledger::where('id', '!=', $data->id)
            ->select('name', 'code')
            ->get();
        
        $ExistingTdsSections = Ledger::select('ledger_group_id', 'tds_section')
            ->whereNotNull('tds_section')
            ->where('tds_section', '!=', '')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($ledger) {
                $groupIds = json_decode($ledger->ledger_group_id, true) ?? [];
                return [
                    'ledger_group_ids' => $groupIds,
                    'tds_section' => $ledger->tds_section
                ];
            });

        $parentUrl = ConstantHelper::LEDGERS_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $itemCodeType = 'Manual';
        if ($services && $services['current_book']) {
            if (isset($services['current_book'])) {
                $book = $services['current_book'];
                if ($book) {
                    $parameters = new stdClass();
                    foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                        $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                        $parameters->{$paramName} = $param;
                    }
                    if (isset($parameters->ledger_code_type) && is_array($parameters->ledger_code_type)) {
                        $itemCodeType = $parameters->ledger_code_type[0] ?? null;
                    }
                }
            }
        }
        if ($request->has('revisionNumber')) {
            $revNo = intval($request->revisionNumber);
        } else {
            $revNo = $data->revision_number;
        }

        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplayLedger($data->book_id, $data->document_status, $data->id, 1, $data->approval_level, $data->created_by ?? 0, $userType['type'], $revNo);


        $docValue = 0;
        $approvalHistory = Helper::getApprovalHistory($data->book_id, $id, $revNo);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '';
        return view('ledgers.edit_ledger', compact(
            'groups',
            'itemCodeType',
            'data',
            'costCenters',
            'groupsModal',
            'gst_group_id',
            'tds_group_id',
            'tcs_group_id',
            'existingLedgers',
            'approvalHistory',
            'buttons',
            'docStatusClass',
            'ExistingTdsSections'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $update = Ledger::find($id);

        $revisionData = [
            ['model_type' => 'header', 'model_name' => 'Ledger', 'relation_column' => ''],
        ];
        $authOrganization = Helper::getAuthenticatedUser()->organization;
        $organizationId = $authOrganization->id;
        $companyId = $authOrganization?->company_id;
        $groupId = $authOrganization?->group_id;

        $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Helper::uniqueRuleWithConditions('erp_ledgers', [
                    'organization_id' => $organizationId,
                    'company_id' => $companyId,
                    'group_id' => $groupId
                ], $id, 'id', false),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Helper::uniqueRuleWithConditions('erp_ledgers', [
                    'organization_id' => $organizationId,
                    'company_id' => $companyId,
                    'group_id' => $groupId
                ], $id),
            ],
            'tax_type' => [
                'nullable',
                'string',
                'max:255',
            ],
            'tax_percentage' => [
                'nullable',
                'numeric',
                'max:255',
            ],
            'tcs_capping' => [
                'nullable',
                'numeric',
            ],
            'tds_capping' => [
                'nullable',
                'numeric',
            ],

            'tds_section' => [
                'nullable',
                'string',
                'max:255',
            ],
            'tds_percentage' => [
                'nullable',
                'numeric',
                'max:255',
            ],
            'tds_without_pan' => [
                'nullable',
                'numeric',
                'max:255',
            ],
            'tcs_section' => [
                'nullable',
                'string',
                'max:255',
            ],
            'tcs_percentage' => [
                'nullable',
                'numeric',
                'max:255',
            ],
        ]);
        $existingName = Ledger::where('name', $request->name)
            ->where('id', '!=', $id)
            ->first();


        $existingCode = Ledger::where('code', $request->code)
            ->where('id', '!=', $id)
            ->first();

        if ($existingName) {
            return back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
        }

        if ($existingCode) {
            return back()->withErrors(['code' => 'The code has already been taken.'])->withInput();
        }


        $request->merge([
            'ledger_group_id' => isset($request->ledger_group_id) ? json_encode($request->ledger_group_id) : null,
        ]);
        $ledgerGroupIds = $request->ledger_group_id ?? [];
        $groupNames = Helper::getGroupsQuery()->whereIn('id', (array) json_decode($ledgerGroupIds))
            ->pluck('name')
            ->map(function ($name) {
                return strtolower(trim($name));
            })
            ->toArray();


        // Clean out unnecessary fields
        if (!in_array('tds', $groupNames)) {
            $request->request->remove('tds_section');
            $request->request->remove('tds_percentage');
            $request->request->remove('tds_capping');
        }

        if (!in_array('tcs', $groupNames)) {
            $request->request->remove('tcs_section');
            $request->request->remove('tcs_percentage');
            $request->request->remove('tcs_capping');
        }

        if (!in_array('gst', $groupNames)) {
            $request->request->remove('tax_type');
            $request->request->remove('tax_percentage');
        }


        $update->name = $request->name;
        $update->prefix = $request->prefix;
        $update->code = $request->code;
        $update->cost_center_id = $request->cost_center_id;
        $update->ledger_group_id = $request->ledger_group_id;
        $update->status = $request->status == 'on' ? 1 : 0;
        $update->tax_type = $request->tax_type ?? null;
        $update->tax_percentage = $request->tax_percentage ?? null;
        $update->tds_section = $request->tds_section ?? null;
        $update->tds_percentage = $request->tds_percentage ?? null;
        $update->tcs_section = $request->tcs_section ?? null;
        $update->tds_capping = $request->tds_capping ?? null;
        $update->tcs_capping = $request->tcs_capping ?? null;
        $update->tcs_percentage = $request->tcs_percentage ?? null;

        if ($request->actionType == "amendment") {
            Helper::documentAmendment($revisionData, $id);
            Helper::approveDocument($update->book_id, $update->id, $update->revision_number, $request->amend_remarks, $request->file('amend_attachments'), $update->approval_level, 'amendment', 0, get_class($update));
            $update->revision_number = $update->revision_number + 1;
            $update->save();
            $bookId = $update->book_id;
            $docId = $update->id;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments') ?? null;
            $currentLevel = 1;
            $revisionNumber = $update->revision_number ?? 0;
            $actionType = 'submit';
            $modelName = get_class($update);
            $totalValue = 0;
            Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
        } else {
            $bookId = $update->book_id;
            $docId = $update->id;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments') ?? null;
            $currentLevel = 1;
            $revisionNumber = $update->revision_number ?? 0;
            $actionType = 'submit';
            $modelName = get_class($update);
            $totalValue = 0;

            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
            $document_status = $approveDocument['approvalStatus'];
            $update->document_status = $document_status;
            if (!in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                $update->status = 0;
            } else {
                $update->status = 1;
            }
            $update->save();
        }

        $updatedGroups = json_decode($request->updated_groups, true); // Decode as an associative array


        if (is_array($updatedGroups)) {
            foreach ($updatedGroups as $group) {
                // Ensure it has necessary keys before using
                if (isset($group['removeGroup'], $group['removeGroupName'], $group['updatedGroup'])) {
                    if ($group['removeGroup'] != "0") {
                        self::updateVoucherGroups((int) $id, (int) $group['removeGroup'], (int) $group['updatedGroup']);
                    }
                }
            }
        }

        if ($request->has('prefix') && $request->prefix != "")
            Group::updatePrefix($update->id, $request->prefix);

        return redirect()->route('ledgers.index')->with('success', 'Ledger updated successfully');

    }

    public function getLedgerGroups(Request $request, $ledgerId)
    {
        $ledgerId = $request->input('ledger_id') ?? $ledgerId;

        $ledger = Ledger::find($ledgerId);

        if ($ledger) {
            $groups = $ledger->group();

            if ($groups && $groups instanceof \Illuminate\Database\Eloquent\Collection) {
                $groupItems = $groups->map(function ($group) {
                    return ['id' => $group->id, 'name' => $group->name];
                });
            } else if ($groups) {
                $groupItems = [
                    ['id' => $groups->id, 'name' => $groups->name],
                ];
            } else {
                $groupItems = [];
            }
            return response()->json($groupItems);
        }

        return response()->json([], 404);
    }
    public function getLedger(Request $request)
    {
        $searchTerm = $request->input('q', '');

        $query = Ledger::where('status', 1);
        if (!empty($searchTerm)) {
            $query->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%$searchTerm%")
                    ->orWhere('code', 'LIKE', "%$searchTerm%");
            });
        }
        $results = $query->limit(10)->get(['id', 'code', 'name']);

        return response()->json($results);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroys(string $id)
    {
        $record = Ledger::findOrFail($id);
        $record->delete();
        return redirect()->route('ledgers.index')->with('success', 'Ledger deleted successfully');
    }
    public function destroy($id)
    {
        try {
            $ledger = Ledger::findOrFail($id);
            $referenceTables = [
                'erp_banks' => ['ledger_id'],
                'erp_bank_details' => ['ledger_id'],
                'erp_cogs_accounts' => ['ledger_id'],
                'erp_discount_master' => ['discount_ledger_id'],
                'erp_so_item_delivery' => ['ledger_id'],
                'erp_customers' => ['ledger_id'],
                'erp_expense_master' => ['expense_ledger_id'],
                'erp_finance_fixed_asset_registration' => ['ledger_id'],
                'erp_item_details' => ['ledger_id'],
                'erp_gr_accounts' => ['ledger_id'],
                'erp_item_details_history' => ['ledger_id'],
                'erp_loan_financial_accounts' => ['pro_ledger_id', 'dis_ledger_id', 'int_ledger_id', 'wri_ledger_id'],
                'erp_payment_vouchers' => ['ledger_id'],
                'erp_sales_accounts' => ['ledger_id'],
                'erp_stock_accounts' => ['ledger_id'],
                'erp_tax_details' => ['ledger_id'],
                'erp_vendors' => ['ledger_id'],
            ];

            $result = $ledger->deleteWithReferences($referenceTables);

            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'referenced_tables' => $result['referenced_tables'] ?? []
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => "Ledger Deleted Successfully"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the ledger: ' . $e->getMessage()
            ], 500);
        }
    }
    public static function updateVoucherGroups($ledger_id, $ledger_group, $updated_ledger_group)
    {
        $organization_id = Helper::getAuthenticatedUser()->organization_id;
        $vouchers = ItemDetail::withWhereHas('voucher', function ($q) use ($organization_id) {
            $q->where('organization_id', $organization_id);
        })->where('ledger_id', $ledger_id)->where('ledger_parent_id', $ledger_group)->get();

        foreach ($vouchers as $voucher) {
            $voucher->update([
                'ledger_parent_id' => $updated_ledger_group, // Replace with actual column names and values
            ]);
        }
        $payments = PaymentVoucherDetails::withWhereHas('voucher', function ($q) use ($organization_id) {
            $q->where('organization_id', $organization_id);
        })->where('ledger_id', $ledger_id)->where('ledger_group_id', $ledger_group)->get();
        foreach ($payments as $payment) {
            $payment->update([
                'ledger_group_id' => $updated_ledger_group, // Replace with actual column names and values
            ]);
        }
    }

    public static function generateLedgerCode(Request $request)
    {
        if (!$request->has('group_id') || $request->group_id == "")
            return "";

        $itemInitials = Group::getPrefix($request->group_id);
        $itemId = $request->input('ledger_id');
        $group_id = $request->input('group_id');
        $group = Group::find($group_id)?->name;

        $baseCode = $itemInitials;

        $authUser = Helper::getAuthenticatedUser();
        $organizationId = $authUser->organization_id;
        if ($itemId) {
            $existingItem = Ledger::find($itemId);
            if ($existingItem) {
                $existingItemCode = $existingItem->code;
                $currentBaseCode = substr($existingItemCode, 0, strlen($baseCode));
                if ($currentBaseCode === $baseCode) {
                    return response()->json(['code' => $existingItemCode]);
                }
            }
        }

        $nextSuffix = '001';
        $finalItemCode = $baseCode . $nextSuffix;

        while (
            Ledger::where('code', $finalItemCode)
                ->exists()
        ) {
            $nextSuffix = str_pad(intval($nextSuffix) + 1, 3, '0', STR_PAD_LEFT);
            $finalItemCode = $baseCode . $nextSuffix;
        }

        return response()->json(['code' => $finalItemCode, 'prefix' => $baseCode]);
    }
    public static function generateLedgerCodeIm($group)
    {
        $groupIds = [];
        if (empty($group))
            return null;
        $groupParts = array_map('trim', explode(',', $group));
        $groupLower = array_map('strtolower', $groupParts);

        $existingGroups = Helper::getGroupsQuery()
            ->whereIn('name', $groupParts)
            ->pluck('name', 'id')
            ->toArray();

        $groupIds = array_keys($existingGroups);
        if (empty($groupIds))
            return null;

        $group_id = $groupIds[0];
        $itemInitials = Group::getPrefix($group_id);
        $baseCode = $itemInitials;
        $nextSuffix = '001';
        $finalItemCode = $baseCode . $nextSuffix;

        while (
            Ledger::where('code', $finalItemCode)
                ->exists()
        ) {
            $nextSuffix = str_pad(intval($nextSuffix) + 1, 3, '0', STR_PAD_LEFT);
            $finalItemCode = $baseCode . $nextSuffix;
        }

        return $finalItemCode;
    }

    public function amendment(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $voucher = Ledger::find($id);
            if (!$voucher) {
                return response()->json(['data' => [], 'message' => "Ledger not found.", 'status' => 404]);
            }

            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'Ledger', 'relation_column' => ''],
            ];

            $a = Helper::documentAmendment($revisionData, $id);
            if ($a) {
                Helper::approveDocument($voucher->book_id, $voucher->id, $voucher->revision_number, 'Amendment', $request->file('attachment') ?? null, $voucher->approvalLevel, 'amendment');
                $voucher->revision_number = $voucher->revision_number + 1;
                $voucher->save();
            }

            DB::commit();
            return response()->json(['data' => [], 'message' => "Amendment done!", 'status' => 200]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Amendment Submit Error: ' . $e->getMessage());
            return response()->json(['data' => [], 'message' => "An unexpected error occurred. Please try again.", 'status' => 500]);
        }
    }

    public function approveVoucher(Request $request)
    {
        $actionType = $request->action_type;

        DB::beginTransaction();
        try {
            $saleOrder = Ledger::find($request->id);
            $bookId = $saleOrder->book_id;
            $docId = $saleOrder->id;
            $docValue = $saleOrder->amount ?? 0;
            $remarks = $request->remarks ?? "";
            $attachments = $request->file('attachment') ?? null;
            $currentLevel = $saleOrder->approval_level;
            $revisionNumber = $saleOrder->revision_number ?? 0;
            // Approve or reject
            $modelName = get_class($saleOrder);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $saleOrder->document_status = $approveDocument['approvalStatus'];
            $saleOrder->approval_level = $approveDocument['nextLevel'];
            $document_status = $approveDocument['approvalStatus'];
            if (!in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                $saleOrder->status = 0;
            } else {
                $saleOrder->status = 1;
            }
            $saleOrder->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $saleOrder,
                'status' => 200
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateNull()
    {
        $user = Helper::getAuthenticatedUser();
        $superadmin = AuthUser::where('user_type', 'IAM-SUPER')
            ->where('organization_id', $user->organization_id)
            ->first();

        $book_id = null;
        $parentUrl = ConstantHelper::LEDGERS_SERVICE_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

        if (!empty($services['current_book'])) {
            $book_id = $services['current_book']->id;
        }

        $response = [
            'superadmin_found' => !empty($superadmin),
            'book_id_found' => !empty($book_id),
            'updates' => []
        ];

        if (!empty($superadmin)) {
            $rowsToUpdateCreatedBy = Ledger::whereNull('created_by')->get(['code']);
            $updatedCreatedBy = Ledger::whereNull('created_by')->update(['created_by' => $superadmin->id]);

            $response['updates']['created_by'] = [
                'updated_count' => $updatedCreatedBy,
                'updated_ledgers' => $rowsToUpdateCreatedBy->pluck('code')
            ];
        } else {
            $response['updates']['created_by'] = 'Superadmin not found';
        }

        if (!empty($book_id)) {
            $rowsToUpdate = Ledger::whereNull('book_id')->get(['code']);
            $updatedCount = Ledger::whereNull('book_id')->update(['book_id' => $book_id]);

            $response['updates']['book_id'] = [
                'updated_count' => $updatedCount,
                'updated_ledgers' => $rowsToUpdate->pluck('code'),
            ];
        } else {
            $response['updates']['book_id'] = 'Book ID not found';
        }

        $rowsToUpdateStatus = Ledger::whereNull('document_status')->get(['code']);
        $updatedStatus = Ledger::whereNull('document_status')->update(['document_status' => 'approved']);

        $response['updates']['document_status'] = [
            'updated_count' => $updatedStatus,
            'updated_ledgers' => $rowsToUpdateStatus->pluck('code')
        ];

        return response()->json($response);
    }
    public function createPartyLedger(Request $req)
    {
        if ($req->has(['code', 'name', 'type','group_id'])) {
            $result = Helper::createPartyLedger($req->type, $req->name, $req->code,$req->group_id);

            return response()->json($result);
        }

        return response()->json([
            'success' => false,
            'message' => 'Missing required fields: code, name, type or group_id.',
            'data' => []
        ], 400);
    }

}
