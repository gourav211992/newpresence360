<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\FinancialPostingHelper;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use App\Helpers\SaleModuleHelper;
use App\Models\ErpAddress;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\VoucherController;
use App\Models\Bank;
use App\Models\CostGroup;
use App\Models\BankDetail;
use App\Models\ErpStore;
use App\Models\ApprovalWorkflow;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Services\Mailers\Mailer;
use App\Helpers\InventoryHelper;
use App\Models\MailBox;
use App\Exports\PaymentReceiptReportExport;
use App\Models\CostCenterOrgLocations;
use App\Models\AuthUser;
use App\Models\Book;
use App\Models\BookType;
use App\Models\CostCenter;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\PaymentVoucher;
use App\Models\VoucherReference;
use App\Models\PaymentVoucherDetails;
use App\Models\PaymentVoucherHistory;
use Illuminate\Support\Facades\Response;

use App\Models\User;
use PDF;
use App\Models\Employee;

use App\Exceptions\ApiGenericException;
use App\Models\Address;
use App\Models\Vendor;
use App\Models\Voucher;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;
use Carbon\Carbon;

class PaymentVoucherController extends Controller
{
    public function amendment(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $voucher = PaymentVoucher::find($id);
            if (!$voucher) {
                return response()->json(['data' => [], 'message' => "Payment Voucher not found.", 'status' => 404]);
            }

            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'PaymentVoucher', 'relation_column' => ''],
                ['model_type' => 'detail', 'model_name' => 'PaymentVoucherDetails', 'relation_column' => 'payment_voucher_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'VoucherReference', 'relation_column' => 'voucher_details_id']
            ];

            $a = Helper::documentAmendment($revisionData, $id);
            if ($a) {
                Helper::approveDocument($voucher->book_id, $voucher->id, $voucher->revision_number, 'Amendment', $request->file('attachment'), $voucher->approval_level, 'amendment');

                $voucher->document_status = ConstantHelper::DRAFT;
                $voucher->revision_number = $voucher->revision_number + 1;
                $voucher->revision_date = now();
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

    public function approvePaymentVoucher(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $saleOrder = PaymentVoucher::find($request->id);
            $bookId = $saleOrder->book_id;
            $docId = $saleOrder->id;
            $docValue = $saleOrder->amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $saleOrder->approval_level;
            $revisionNumber = $saleOrder->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($saleOrder);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $saleOrder->approvalLevel = $approveDocument['nextLevel'];
            $saleOrder->document_status = $approveDocument['approvalStatus'];
            $saleOrder->approvalStatus = $approveDocument['approvalStatus'];
            $saleOrder->approval_level = $approveDocument['nextLevel'];
            $saleOrder->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $saleOrder,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType document.",
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function getParties(Request $r)
    {
        $ledger_account = $r->type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? ConstantHelper::RECEIVABLE : ConstantHelper::PAYABLE;
        $ledger_group = Helper::getGroupsQuery()->where('name', $ledger_account)->first();

        $ids = [];
        $group_id = $ledger_group->getAllChildIds();
        $group_id[] = $ledger_group->id;

        // Determine relation and alias
        $relation = $r->type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? 'customer' : 'vendor';



        $data = Ledger::with([
            $relation => function ($query) use ($r) {
                $query->when($r->type === ConstantHelper::PAYMENTS_SERVICE_ALIAS, function ($q) {
                    $q->withoutGlobalScope(DefaultGroupCompanyOrgScope::class);
                });
            }
        ])
            ->where(function ($query) use ($group_id) {
                $query->where(function ($q) use ($group_id) {
                    foreach ($group_id as $id) {
                        $q->orWhereJsonContains('ledger_group_id', (string) $id)
                            ->orWhereJsonContains('ledger_group_id', $id);
                    }
                    $q->orWhereIn('ledger_group_id', $group_id);
                });

                $query->where('status', 1);
            });




        if ($r->keyword) {
            $data->where('name', 'LIKE', "%{$r->keyword}%");
        }

        $data->select('id', 'name', 'code', 'status');


        // $data = $data->where('status', 1);
        if ($r->ids) {
            $ids = array_map('intval', $r->ids);
        }
        $data = $data->get()
            ->reject(fn($customer) => in_array($customer->id, $ids))
            ->map(fn($customer) => [
                'value' => $customer->id,
                'label' => $customer->name,
                'code' => $customer->code,
                'customer' => $customer->customer,
                'vendor' => $customer->vendor,
                'organization' => $customer->vendor->organization ?? $customer->organization,
            ])
            ->toArray();


        return response()->json($data);
    }

    public function index(Request $request, $type = "Payment")
    {

        $user = Helper::getAuthenticatedUser();
        $userId = $user->auth_user_id;
        $organizationId = $user->organization_id;
        $organizations = [];
        $parentURL = request()->segments()[0];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }


        $createRoute = route('payments.create');
        $editRouteString = 'payments.edit';
        if ($parentURL === 'payments') {
            $type = ConstantHelper::PAYMENTS_SERVICE_ALIAS;
            $createRoute = route('payments.create');
            $editRouteString = 'payments.edit';
        }
        if ($parentURL === 'receipts') {
            $type = ConstantHelper::RECEIPTS_SERVICE_ALIAS;
            $createRoute = route('receipts.create');
            $editRouteString = 'receipts.edit';
        }
        request()->merge(['type' => $type]);

        // Check if `filter_organization` is set and push values to `$organizations`
        if ($request->filter_organization && is_array($request->filter_organization)) {
            foreach ($request->filter_organization as $value) {
                $organizations[] = $value;
            }
        }

        // If no organizations are selected, use the authenticated user's organization
        if (count($organizations) == 0) {
            $organizations[] = $organizationId;
        }

        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        $accessibleLocations = InventoryHelper::getAccessibleLocations();
        $locationIds = $accessibleLocations->pluck('id')->toArray();
        $cost_center_ids = null;
        if (!empty($request->cost_center_id)) {
            $cost_center_ids = $request->cost_center_id ?? null;
        } elseif (!empty($request->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $request->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
        }
        // Retrieve vouchers based on organization_id and include series with levels
        $data = PaymentVoucher::with([
            'series' => function ($d) {
                $d->select('id', 'book_name');
            },
            'bank' => function ($d) {
                $d->select('id', 'bank_name as name');
            },
            'ledger' => function ($d) {
                $d->select('id', 'name');
            },
            'currency' => function ($d) {
                $d->select('id', 'name', 'short_name');
            }
        ])->where('document_status', '!=', 'cancel')
            ->whereIn('location', $locationIds);

        // Apply filters based on the request
        // if ($request->document_type) {
        $data = $data->where('document_type', $type);
        // }

        if ($request->document_no) {
            $data = $data->where('voucher_no', 'like', "%" . $request->document_no . "%");
        }

        if ($request->bank_id) {
            $data = $data->where('bank_id', $request->bank_id);
        }

        if ($request->ledger_id) {
            $data = $data->where('ledger_id', $request->ledger_id);
        }
        // if ($request->cost_center_id) {
        //     $data = $data->where('cost_center_id', $request->cost_center_id);
        // }
        if (!empty($cost_center_ids)) {
            if (is_array($cost_center_ids)) {
                $data = $data->whereIn('cost_center_id', $cost_center_ids);
            } else {
                $data = $data->where('cost_center_id', $cost_center_ids);
            }
        }
        if ($request->location_id) {
            $data = $data->where('location', $request->location_id);
        }
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            // $end = date('Y-m-d', strtotime($dates[1]));
            $end = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $start;
            $data = $data->whereDate('document_date', '>=', $start)->whereDate('document_date', '<=', $end);
        } else {
            $data = $data->whereDate('document_date', '>=', $fyear['start_date'])
                ->whereDate('document_date', '<=', $fyear['end_date']);
            $start = $fyear['start_date'];
            $end = $fyear['end_date'];
        }

        $data = $data->orderBy('document_date', 'desc')->orderBy('created_at', 'desc')->get();

        // return response()->json($data);

        $mappings =Helper::access_org();

        $book_type = $request->book_type;
        $date = $request->date;
        $document_no = $request->document_no;
        $bank_id = $request->bank_id;
        $ledger_id = $request->ledger_id;
        $document_type = $request->document_type;
        $cost_center = $request->cost_center_id;
        $banks = Bank::withWhereHas('bankDetails')->get();
        $groupId = Helper::getGroupsQuery()->where('name', 'Cash-in-Hand')->value('id');

        $ledgers = Ledger::where(function ($query) use ($groupId) {
            $query->whereJsonContains('ledger_group_id', (string) $groupId)
                ->orWhere('ledger_group_id', $groupId);
        })->select('id', 'name')->get();
        $date = $request->date ?? Carbon::parse($fyear['start_date'])->format('d-m-Y') . " to " . Carbon::parse($fyear['end_date'])->format('d-m-Y');
        $date2 = Carbon::parse($start)->format('jS-F-Y') . ' to ' . Carbon::parse($end)->format('jS-F-Y');

        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status', 'active')->get()->toArray();
        $fyearLocked = $fyear['authorized'];
        $locations = InventoryHelper::getAccessibleLocations();
        return view('paymentVoucher.paymentVouchers', compact('cost_centers', 'mappings', 'banks', 'ledgers', 'bank_id', 'ledger_id', 'organizationId', 'data', 'book_type', 'date', 'document_no', 'document_type', 'type', 'createRoute', 'editRouteString', 'date', 'date2', 'fyearLocked', 'locations', 'cost_groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $r)
    {
        // $serviceAlias = [ConstantHelper::PAYMENT_VOUCHER_RECEIPT];
        $parentURL = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        // $redirectUrl = route('payments.index');
        $storeUrl = route('payments.store');
        if ($parentURL === 'payments') {
            $type = ConstantHelper::PAYMENTS_SERVICE_ALIAS;
            $redirectUrl = route('payments.index');
            $storeUrl = route('payments.store');
        }
        if ($parentURL === 'receipts') {
            $type = ConstantHelper::RECEIPTS_SERVICE_ALIAS;
            $redirectUrl = route('receipts.index');
            $storeUrl = route('receipts.store');
        }
        request()->merge(['type' => $type]);
        $firstService = $servicesBooks['services'][0];
        $books = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $books_t = Helper::getAccessibleServicesFromMenuAlias('vouchers')['services'];
        $banks = Bank::withWhereHas('bankDetails')->get();

        $groupId = Helper::getGroupsQuery()->where('name', 'Cash-in-Hand')->value('id');

        $ledgers = Ledger::where(function ($query) use ($groupId) {
            $query->whereJsonContains('ledger_group_id', (string) $groupId) // Cast groupId to string
                ->orWhere('ledger_group_id', $groupId);
        })->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'name', 'short_name')->get();

        $orgCurrency = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->value('currency_id');

        $cost_centers = Helper::getActiveCostCenters();
        // pass authenticate user's org locations
        $locations = InventoryHelper::getAccessibleLocations();
        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        $token = $r->query('token');
        $cached = Cache::get($token, ['grouped' => [], 'raw' => []]);

        $selectedRows = $cached['grouped'];
        $fy_months = Helper::getCurrentFinancialYearMonths();
        $rawItemData = $cached['raw'];
        return view(
            'paymentVoucher.createPaymentVoucher',
            compact(
                'cost_centers',
                'books_t',
                'books',
                'banks',
                'ledgers',
                'currencies',
                'orgCurrency',
                'type',
                'storeUrl',
                'redirectUrl',
                'locations',
                'fyear',
                'selectedRows',
                'rawItemData',
                'fy_months'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->date);
        if (!isset($numberPatternData)) {
            return response()->json([
                'message' => "Invalid Book",
                'error' => "",
            ], 422);
        }



        $voucherExists = PaymentVoucher::where('voucher_no', $numberPatternData['document_number'])
            ->where('book_id', $request->book_id)->exists();

        $selected_token = $request->selected_token;
        if ($voucherExists) {
            return redirect()
                ->route($request->document_type . '.create', ['token' => $selected_token])
                ->withErrors(['voucher_no' => $request->voucher_no . ' Voucher No. Already Exist!']);
        }
        // if($request->reference_no!="" && $request->payment_type === "Bank"){
// $ref = PaymentVoucher::where('reference_no', $request->reference_no)->exists();

        // if ($ref) {
//     return redirect()
//         ->route($request->document_type . '.create')
//         ->withErrors(['Reference No. Already Exist!'])->withInput();
// }
// }



        // Begin transaction
        DB::beginTransaction();

        try {
            // Code goes here as you already have

            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
            $voucher = new PaymentVoucher();

            $status = $request->status;
            $book = Book::find($request->book_id);
            $voucher->book_id = $request->book_id;
            $voucher->bookCode = $book->book_code;

            // Save voucher details
            $voucher->voucher_no = $numberPatternData['document_number'];
            $voucher->document_type = $request->document_type;
            $voucher->date = $request->date;
            $voucher->payment_type = $request->payment_type;
            $voucher->bank_id = $request->bank_id;
            $voucher->cost_center_id = $request->cost_center_id ?? null;


            if ($request->payment_type === "Bank") {
                $bank = Bank::find($request->bank_id);
                $voucher->ledger_id = $bank->ledger_id;
                $voucher->ledger_group_id = $bank->ledger_group_id;
                $voucher->bankCode = $bank->bank_code;
                if ($request->account_id) {
                    $account = BankDetail::find($request->account_id);
                    $voucher->accountNo = $account->account_number;
                    $voucher->account_id = $request->account_id;
                }
            } else {
                $groupId = Helper::getGroupsQuery()->where('name', 'Cash-in-Hand')->value('id');
                $voucher->ledger_id = $request->ledger_id;
                $voucher->ledger_group_id = $groupId;
            }

            $voucher->payment_date = $request->payment_date;
            $voucher->payment_mode = $request->payment_mode;
            // $voucher->reference_no = $request->payment_type === "Bank"?$request->reference_no:"";
            $voucher->revision_number = 0;

            // Currency details
            $voucher->currency_id = $request->currency_id;
            $currency = Currency::find($request->currency_id);
            $voucher->currencyCode = $currency->short_name;
            $voucher->org_currency_id = $request->org_currency_id;
            $voucher->org_currency_code = $request->org_currency_code;
            $voucher->org_currency_exg_rate = $request->org_currency_exg_rate;
            $voucher->comp_currency_id = $request->comp_currency_id;
            $voucher->comp_currency_code = $request->comp_currency_code;
            $voucher->comp_currency_exg_rate = $request->comp_currency_exg_rate;
            $voucher->group_currency_id = $request->group_currency_id;
            $voucher->group_currency_code = $request->group_currency_code;
            $voucher->group_currency_exg_rate = $request->group_currency_exg_rate;
            $voucher->location = $request->location;


            // Payment amount and organization details
            $voucher->amount = $request->totalAmount;
            $voucher->organization_id = $organization->id;
            $voucher->group_id = $organization->group_id;
            $voucher->company_id = $organization->company_id;

            // Document details
            $voucher->document_date = $request->date;
            $voucher->doc_no = $numberPatternData['doc_no'];
            $voucher->doc_number_type = $numberPatternData['type'];
            $voucher->doc_reset_pattern = $numberPatternData['reset_pattern'];
            $voucher->doc_prefix = $numberPatternData['prefix'];
            $voucher->doc_suffix = $numberPatternData['suffix'];
            $voucher->approvalLevel = 1;
            $voucher->document_status = $status;
            $voucher->remarks = $request->remarks;

            // Handle file upload
            if ($request->hasFile('document')) {
                $fileName = time() . '_' . $request->file('document')->getClientOriginalName();
                $destinationPath = public_path('voucherPaymentDocuments');
                $request->file('document')->move($destinationPath, $fileName);
                $voucher->document = $fileName;
            }

            // Save voucher
            $voucher->user_id = $user->auth_user_id;
            $voucher->user_type = $user->authenticable_type;
            $voucher->created_by = Helper::getAuthenticatedUser()->auth_user_id;
            // dd($voucher);
            $voucher->save();
            // Process voucher details
            foreach ($request->party_id as $index => $party) {
                $details = new PaymentVoucherDetails();
                if ($request->reference_no && $request->reference_no[$index] != "" && $request->payment_type === "Bank") {
                    $ref = PaymentVoucherDetails::where('reference_no', $request->reference_no[$index])->exists();

                    if ($ref) {
                        return redirect()
                            ->route($request->document_type . '.create', ['token' => $selected_token])
                            ->withErrors(['Reference No. Already Exist!'])->withInput();
                    }
                }
                $details->payment_voucher_id = $voucher->id;
                $customer = Ledger::find($party);
                $details->ledger_id = $customer->id;
                $details->ledger_group_id = $request->parent_ledger_id[$index];
                $details->partyCode = $customer->code;
                $details->type = $request->document_type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? "customer" : "vendor";
                $details->currentAmount = $request->amount[$index];
                $details->reference_no = $request->reference_no[$index];
                $details->orgAmount = $request->amount_exc[$index];
                $details->reference = $request->reference[$index];
                $details->save();

                if ($request->reference[$index] == "Invoice") {
                    foreach (json_decode($request->party_vouchers[$index]) as $reference) {
                        $blnc = self::getVoucherBalance($reference->amount, $reference->voucher_id, $request->document_type, $details->ledger_id, $details->ledger_group_id);
                        if ($blnc < 0) {
                            $voucher = Voucher::find($reference->voucher_id)?->voucher_no;
                            return redirect()->route($request->document_type . '.create', ['token' => $selected_token])
                                ->withErrors("The settled amount exceeds the balance amount for Voucher No." . $voucher);
                        } else {
                            $insertRef = new VoucherReference();
                            $insertRef->payment_voucher_id = $voucher->id;
                            $insertRef->voucher_details_id = $details->id;
                            $insertRef->party_id = $reference->party_id;
                            $insertRef->voucher_id = $reference->voucher_id;
                            $insertRef->amount = $reference->amount;
                            $insertRef->save();

                        }
                    }
                }

                // Handle approval process
                $voucher = PaymentVoucher::find($voucher->id);
                if ($voucher->document_status == ConstantHelper::SUBMITTED) {
                    $approveDocument = Helper::approveDocument($voucher->book_id, $voucher->id, $voucher->revision_number, $voucher->remarks, $request->file('attachment'), 1, 'submit', $voucher->amount, get_class($voucher));
                    $voucher->document_status = $approveDocument['approvalStatus'] ?? ConstantHelper::SUBMITTED;
                    $voucher->save();

                }
            }

            // Commit transaction
            DB::commit();

            // Return response
            if ($voucher->document_type === ConstantHelper::PAYMENTS_SERVICE_ALIAS) {
                return redirect()->route("payments.index")->with('success', __('message.created', ['module' => 'Payment Voucher']));
            } else {
                return redirect()->route("receipts.index")->with('success', __('message.created', ['module' => 'Payment Voucher']));
            }
        } catch (Exception $e) {
            // Rollback transaction if any error occurs
            DB::rollback();
            // dd($e->getMessage());

            // Log the error or return a response
            return redirect()
                ->route($request->document_type . '.create', ['token' => $selected_token])
                ->withErrors('Error occurred: ' . $e->getMessage());
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $r, $payment)
    {
        $id = $payment;
        $parentURL = request()->segments()[0];
        $editUrl = 'payments.update';
        $indexUrl = route('payments.index');
        $editUrlString = 'payments.edit';
        if ($parentURL === 'payments') {
            $editUrl = 'payments.update';
            $indexUrl = route('payments.index');
            $editUrlString = 'payments.edit';
        }
        if ($parentURL === 'receipts') {
            $editUrl = 'receipts.update';
            $indexUrl = route('receipts.index');
            $editUrlString = 'receipts.edit';
        }
        $currNumber = $r->revisionNumber;
        $data = PaymentVoucher::with('details')->find($id);

        if ($r->has('revisionNumber') && $data->revision_number != $currNumber) {
            $data = PaymentVoucherHistory::with('details')->where('source_id', $id)->where('revision_number', $currNumber)->first();
        } else {
            $data = PaymentVoucher::with('details')->find($id);
        }


        // $serviceAlias = [ConstantHelper::PAYMENT_VOUCHER_RECEIPT];
        $serviceAlias = Helper::getAccessibleServicesFromMenuAlias($parentURL)['services'];
        $books = Helper::getBookSeriesNew(count($serviceAlias) > 0 ? $serviceAlias[0]->alias : '', $parentURL, true)->get();
        $books_t = Helper::getAccessibleServicesFromMenuAlias('vouchers')['services'];


        $buttons = Helper::actionButtonDisplay($data->book_id, $data->document_status, $id, $data->amount, $data->approvalLevel, $data->user_id, $data->user_type);
        if ($data->document_status === ConstantHelper::DRAFT || $data->document_status === ConstantHelper::SUBMITTED)
            $buttons['cancel'] = true;
        else
            $buttons['cancel'] = false;

        if ($data->document_status === ConstantHelper::POSTED)
            $buttons['amend'] = false;

        $revision_number = $data->revision_number;
        $revNo = $data->revision_number;
        if ($r->has('revisionNumber')) {
            $revNo = intval($r->revisionNumber);
        } else {
            $revNo = $data->revision_number;
        }

        $history = Helper::getApprovalHistory($data->book_id, $id, $revNo, $data->amount, $data->created_by);

        $banks = Bank::withWhereHas('bankDetails')->get();
        $groupId = Helper::getGroupsQuery()->where('name', 'Cash-in-Hand')->value('id');

        $ledgers = Ledger::where(function ($query) use ($groupId) {
            $query->whereJsonContains('ledger_group_id', (string) $groupId)
                ->orWhere('ledger_group_id', $groupId);
        })->select('id', 'name')->get();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'name', 'short_name')->get();
        $orgCurrency = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->value('currency_id');
        $revisionNumbers = $history->pluck('revision_number')->unique()->values()->all();
        $userData = Helper::getAuthenticatedUser();

        $cc_users = Helper::getOrgWiseUserAndEmployees($userData->organization_id);

        $userchk = Helper::userCheck();


        $to_users = $userData->id;
        $to_user_mail = $userData->email;
        $to_type = $userchk['user_type'];

        $approvalHistory = $history;
        $cc_users = Helper::getOrgWiseUserAndEmployees(Helper::getAuthenticatedUser()->organization_id);
        $model = $data->document_type == 'receipts' ? Customer::class : Vendor::class;
        $to_users = [];

        foreach ($data->details as $detail) {
            $ledger = $detail->ledger_id;
            $group = $detail->ledger_group_id;

            $userData = $model::where('ledger_group_id', $group)
                ->where('ledger_id', $ledger)
                ->first();

            if ($userData) {
                $userObj = new \stdClass();
                $userObj->id = $userData->id;
                $userObj->email = $userData->email;
                $userObj->ledger = $ledger;
                $userObj->group = $group;
                $userObj->type = $model;

                $to_users[] = $userObj;
            }
        }

        $cost_centers = Helper::getActiveCostCenters();



        $locations = InventoryHelper::getAccessibleLocations();
        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        if ($data->document_status == ConstantHelper::DRAFT || ($r->amendment==1 && $buttons['amend']))
            return view('paymentVoucher.editPaymentVoucher', compact('cost_centers', 'books_t', 'data', 'books', 'buttons', 'history', 'banks', 'ledgers', 'currencies', 'orgCurrency', 'revision_number', 'currNumber', 'editUrl', 'indexUrl', 'editUrlString', 'locations', 'fyear'));
        else
            return view('paymentVoucher.viewPaymentVoucher', compact('cost_centers', 'data', 'books_t', 'books', 'buttons', 'history', 'banks', 'ledgers', 'currencies', 'orgCurrency', 'revision_number', 'currNumber', 'editUrl', 'indexUrl', 'editUrlString', 'approvalHistory', 'cc_users', 'to_users', 'to_user_mail', 'to_type', 'locations', 'fyear'));
    }


    public function update(Request $request, string $id)
    {
        // if($request->reference_no!="" && $request->payment_type === "Bank"){
// $ref = PaymentVoucher::where('reference_no', $request->reference_no)->where('id','!=',$id)->exists();

        // if ($ref) {
//     return redirect()
//         ->route($request->document_type . '.edit', [$id])
//         ->withErrors(['Reference No. Already Exist!'])->withInput();
// }
// }

        DB::beginTransaction();

        try {
            $voucher = PaymentVoucher::find($id);
            if ($request->action_type == "amendment") {
                 $revisionData = [
                ['model_type' => 'header', 'model_name' => 'PaymentVoucher', 'relation_column' => ''],
                ['model_type' => 'detail', 'model_name' => 'PaymentVoucherDetails', 'relation_column' => 'payment_voucher_id'],
                ['model_type' => 'sub_detail', 'model_name' => 'VoucherReference', 'relation_column' => 'voucher_details_id']
                ];
                Helper::documentAmendment($revisionData, $id);
                Helper::approveDocument($voucher->book_id, $voucher->id, $voucher->revision_number, $request->amend_remarks, $request->file('amend_attachment'), $voucher->approval_level, 'amendment', 0, get_class($voucher));
                $voucher->revision_number = $voucher->revision_number + 1;
                $voucher->revision_date = now();
                $voucher->save();
            }
            PaymentVoucherDetails::where('payment_voucher_id', $id)->delete();
            VoucherReference::where('payment_voucher_id', $id)->delete();

            foreach ($request->party_id as $index => $party) {
                $details = new PaymentVoucherDetails();
                if ($request->reference_no && $request->reference_no[$index] != "" && $request->payment_type === "Bank") {
                    $ref = PaymentVoucherDetails::where('reference_no', $request->reference_no[$index])->exists();

                    if ($ref) {
                        return redirect()
                            ->route($request->document_type . '.create')
                            ->withErrors(['Reference No. Already Exist!'])->withInput();
                    }
                }
                $details->payment_voucher_id = $voucher->id;
                $customer = Ledger::find($party);
                $details->ledger_id = $customer->id;
                $details->ledger_group_id = $request->parent_ledger_id[$index];
                $details->partyCode = $customer->code;
                $details->type = $request->document_type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? "customer" : "vendor";
                $details->currentAmount = $request->amount[$index];
                $details->reference_no = $request->reference_no[$index];
                $details->orgAmount = $request->amount_exc[$index];
                $details->reference = $request->reference[$index];
                $details->save();

                if ($request->reference[$index] == "Invoice") {
                    $partyVouchers = json_decode($request->party_vouchers[$index], true);

                    if (!is_array($partyVouchers)) {
                        DB::rollBack();
                        return response()->json(['error' => 'Invalid party_vouchers data'], 400);
                    }

                    $insertData = [];
                    foreach ($partyVouchers as $reference) {
                        $diff = self::getVoucherBalance($reference['amount'], $reference['voucher_id'], $request->document_type, $details->ledger_id, $details->ledger_group_id, $id, $details->id);
                        if ($diff < 0) {
                            $voucherNo = Voucher::find($reference['voucher_id'])?->voucher_no;
                            DB::rollBack();
                            return redirect()->route($request->document_type . '.edit', [$id])->withErrors("The settled amount exceeds the balance amount for Voucher No." . $voucherNo);
                        } else {
                            $insertData[] = [
                                'payment_voucher_id' => $voucher->id,
                                'voucher_details_id' => $details->id,
                                'party_id' => $reference['party_id'],
                                'voucher_id' => $reference['voucher_id'],
                                'amount' => $reference['amount'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                        }
                    }

                    if (!empty($insertData)) {
                        VoucherReference::insert($insertData);
                    }
                }
            }

            $voucher->document_type = $request->document_type;
            $voucher->date = $request->date;
            $voucher->payment_type = $request->payment_type;
            $voucher->location = $request->location;
            $status = $request->status;


            if ($voucher->payment_type == "Bank") {
                $voucher->bank_id = $request->bank_id;
                $bank = Bank::find($request->bank_id);
                $voucher->bankCode = $bank->bank_code;

                $voucher->account_id = $request->account_id;
                $account = BankDetail::find($request->account_id);
                $voucher->accountNo = $account->account_number;
                $voucher->payment_mode = $request->payment_mode;
                // $voucher->reference_no = $request->payment_type === "Bank"?$request->reference_no:"";
                $voucher->ledger_id = $bank->ledger_id;
                $voucher->ledger_group_id = $bank->ledger_group_id;
            } else {
                $groupId = Helper::getGroupsQuery()->where('name', 'Cash-in-Hand')->value('id');

                $voucher->ledger_id = $request->ledger_id;
                $voucher->ledger_group_id = $groupId;
                $voucher->account_id = null;
                $voucher->bankCode = null;
                $voucher->accountNo = null;
                $voucher->payment_mode = null;
                $voucher->reference_no = null;
            }

            $voucher->payment_mode = $request->payment_mode;
            $voucher->payment_date = $request->payment_date;

            $voucher->currency_id = $request->currency_id;
            $voucher->cost_center_id = $request->cost_center_id ?? null;
            $currency = Currency::find($request->currency_id);
            $voucher->currencyCode = $currency->short_name;

            $voucher->org_currency_id = $request->org_currency_id;
            $voucher->org_currency_code = $request->org_currency_code;
            $voucher->org_currency_exg_rate = $request->org_currency_exg_rate;

            $voucher->comp_currency_id = $request->comp_currency_id;
            $voucher->comp_currency_code = $request->comp_currency_code;
            $voucher->comp_currency_exg_rate = $request->comp_currency_exg_rate;

            $voucher->group_currency_id = $request->group_currency_id;
            $voucher->group_currency_code = $request->group_currency_code;
            $voucher->group_currency_exg_rate = $request->group_currency_exg_rate;

            $voucher->amount = $request->totalAmount;
            $voucher->remarks = $request->remarks;
            $voucher->document_status = $status;

            if ($request->hasFile('document')) {
                $fileName = time() . '_' . $request->file('document')->getClientOriginalName();
                $destinationPath = public_path('voucherPaymentDocuments');
                $request->file('document')->move($destinationPath, $fileName);
                $voucher->document = $fileName;
            }

            $voucher->save();



            // Reload voucher for approval flow
            $voucher = PaymentVoucher::find($id);
            if ($voucher->document_status == ConstantHelper::SUBMITTED) {
                $approveDocument = Helper::approveDocument($voucher->book_id, $voucher->id, $voucher->revision_number, $voucher->remarks, $request->file('attachment'), 1, 'submit', $voucher->amount, get_class($voucher));
                $voucher->document_status = $approveDocument['approvalStatus'] ?? ConstantHelper::SUBMITTED;
                $voucher->save();

            }

            DB::commit();

            return redirect()->route(
                $voucher->document_type === ConstantHelper::PAYMENTS_SERVICE_ALIAS ? "payments.index" : "receipts.index"
            )->with('success', __('message.created', ['module' => 'Payment Voucher']));

        } catch (Exception $e) {
            DB::rollBack();
            // dd($e->getTraceAsString());
            return back()->withErrors('Something went wrong: ' . $e->getMessage());
        }
    }
    public function testPostingDetails($request)
    {
        try {
            $data = FinancialPostingHelper::receiptVoucherPosting($request->book_id ?? 0, $request->id ?? 0, "get", $request->remarks ?? "No Remarks here...");
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    public function getPostingDetails(Request $request)
    {
        try {
            $data = FinancialPostingHelper::financeVoucherPosting((int) $request->book_id ?? 0, (int) $request->document_id ?? 0, $request->type ?? 'get');

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage() . $ex->getFile() . $ex->getLine()
            ]);
        }
    }

    public function postPostingDetails(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = FinancialPostingHelper::financeVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "post");
            if ($data['status']) {
                $pv = PaymentVoucher::find($request->document_id);
                $pv->document_status = ConstantHelper::POSTED;
                $pv->save();
                DB::commit();
            } else {
                DB::rollBack();
            }
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage()
            ]);
        }
    }
    public function postInvoice($request)
    {
        try {
            DB::beginTransaction();
            $data = FinancialPostingHelper::financeVoucherPosting($request->book_id ?? 0, $request->id ?? 0, "post");
            if ($data['status']) {
                $pv = PaymentVoucher::find($request->id);
                $pv->document_status = ConstantHelper::POSTED;
                $pv->save();
                DB::commit();
            } else {
                DB::rollBack();
                $pv = PaymentVoucher::find($request->id);
                $pv->document_status = ConstantHelper::APPROVED;
                $pv->save();
            }
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'status' => 'exception',
                'message' => 'Some internal error occured',
                'error' => $ex->getMessage()
            ]);
        }
    }

    // public function getPostingDetails(Request $request)
    //     {
    //             try{

    //             if($doc_type==ConstantHelper::RECEIPTS_SERVICE_ALIAS){
    //                 $data = FinancialPostingHelper::receiptVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "get",$request->remarks);
    //                 return response() -> json([
    //                     'status' => 'success',
    //                     'data' => $data
    //                 ]);
    //             }else if($doc_type==ConstantHelper::PAYMENTS_SERVICE_ALIAS){
    //                 $data = FinancialPostingHelper::paymentVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "get",$request->remarks);
    //                 return response() -> json([
    //                     'status' => 'success',
    //                     'data' => $data
    //                 ]);
    //             }
    //             else{
    //                 return response() -> json([
    //                     'status' => 'error',
    //                     'message' => 'Type not set'
    //                 ]);
    //             }
    //         }
    //         catch(Exception $e){
    //                 return response() -> json([
    //                     'status' => 'error',
    //                     'message' => $e->getMessage()
    //                 ]);
    //             }

    //     }
    //     public function postPostingDetails(Request $request)
    //     {
    //         try{
    //             if($doc_type==ConstantHelper::RECEIPTS_SERVICE_ALIAS){
    //             $data = FinancialPostingHelper::receiptVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "post",$request->remarks??"No Remarks Here..");
    //             return response() -> json([
    //                 'status' => 'success',
    //                 'data' => $data
    //             ]);
    //         }else if($doc_type==ConstantHelper::PAYMENTS_SERVICE_ALIAS){
    //             $data = FinancialPostingHelper::paymentVoucherPosting($request->book_id ?? 0, $request->document_id ?? 0, "post",$request->remarks??"No Remarks Here..");
    //             return response() -> json([
    //                 'status' => 'success',
    //                 'data' => $data
    //             ]);
    //         }

    //         else{
    //             return response() -> json([
    //                 'status' => 'error',
    //                 'message' => 'Type not set'
    //             ]);
    //         }
    //         }
    //         catch(Exception $e){
    //             return response() -> json([
    //                 'status' => 'error',
    //                 'message' => $e->getMessage()
    //             ]);
    //         }

    //     }

    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $po = PaymentVoucher::find($request->id);
            if (isset($po)) {
                $revoke = Helper::approveDocument($po->book_id, $po->id, $po->revision_number, '', null, 1, ConstantHelper::REVOKE, $po->amount, get_class($po));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $po->document_status = $revoke['approvalStatus'];
                    $po->save();
                    DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Revoked succesfully',
                    ]);
                }
            } else {
                DB::rollBack();
                throw new ApiGenericException("No Document found");
            }
        } catch (Exception $ex) {
            DB::rollBack();
            throw new ApiGenericException($ex->getMessage());
        }
    }
    public function cancelDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $po = PaymentVoucher::find($request->id);
            if (isset($po)) {
                $revoke = Helper::approveDocument($po->book_id, $po->id, $po->revision_number, '', null, 1, ConstantHelper::CANCEL, $po->amount, get_class($po));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $po->document_status = ConstantHelper::CANCEL;
                    $po->save();
                    DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Canceled succesfully',
                    ]);
                }
            } else {
                DB::rollBack();
                throw new ApiGenericException("No Document found");
            }
        } catch (Exception $ex) {
            DB::rollBack();
            throw new ApiGenericException($ex->getMessage());
        }
    }
    public function check_approved($book_id)
    {
        $workflow = ApprovalWorkFlow::where('book_id', $book_id);
        $user = Helper::getAuthenticatedUser();

        if ($workflow && $workflow->count() == 1) {
            $workflow = $workflow->first();
            if ($workflow->user_id === $user->auth_user_id) {
                return true;
            } else
                return false;
        } else
            return false;
    }
    public static function getPrint($id, $ledger, $group, $details = null)
    {
        try {
            $document = PaymentVoucher::find($id);

            if (!$document) {
                throw new Exception("Payment voucher not found.");
            }

            $type = $document->document_type === "receipts" ? "debit" : "credit";
            $ledger_group = $group;
            $organization_id = Helper::getAuthenticatedUser()->organization_id;

            $ledger_name = Ledger::find($ledger)?->name ?? throw new Exception("Ledger not found.");
            $group_name = Group::find($group)?->name ?? throw new Exception("Group not found.");

            $model = $type == 'debit' ? Customer::class : Vendor::class;

            $credit_days = $model::where('ledger_group_id', $group)
                ->where('ledger_id', $ledger)
                ->value('credit_days');
            $credit_days = $credit_days ?? 0;
            $doc_types = $type === 'debit' ? [ConstantHelper::RECEIPTS_SERVICE_ALIAS, 'Receipt'] : [ConstantHelper::PAYMENTS_SERVICE_ALIAS, 'Payment'];
            $cus_type = $type === 'debit' ? 'customer' : 'vendor';
            $datas = [];

            $vouchers = PaymentVoucherDetails::where('payment_voucher_id', $id)->where('ledger_id', $ledger)->where('ledger_group_id', $group)->get();

            $data = [];
            foreach ($vouchers as $voucher) {
                if ($voucher->reference == "Invoice") {
                    $invoices = VoucherReference::where('voucher_details_id', $voucher->id)->get();

                    foreach ($invoices as $invoice) {
                        $invoice->bill_no = $invoice->voucher->voucher_no;
                        $invoice->date = $voucher->voucher->document_date;
                        $invoice->voucher_amount = $invoice->voucher->amount;
                        $invoice->paid = $invoice->amount;

                        $balance = VoucherReference::where('payment_voucher_id', '<', (int) $id)
                            ->where('voucher_id', $invoice->voucher->id)
                            ->withWhereHas('voucherPayRec', function ($query) {
                                $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
                                $query->where('document_status', ConstantHelper::POSTED);
                            })->where('party_id', $ledger)->sum('amount');


                        $invoice->balance = $invoice->voucher->amount - $balance;
                    }

                    $advanceSum = PaymentVoucherDetails::where('payment_voucher_id', '<', $id)
                        ->whereIn('reference', ['Advance', 'On Account'])
                        ->withWhereHas('voucher', function ($query) {
                            $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id)
                                ->where('document_status', ConstantHelper::POSTED);
                        })
                        ->with('partyName')->get()->filter(function ($adv) use ($ledger, $ledger_group) {
                            if (is_null($adv->ledger_id)) {
                                return $adv->partyName && $adv->partyName->ledger_id == $ledger && $adv->partyName->ledger_group_id == $ledger_group;
                            } else {
                                return $adv->ledger_id == $ledger && $adv->ledger_group_id == $ledger_group;
                            }
                        })->sum('orgAmount');

                    foreach ($invoices as $v) {
                        if ($advanceSum > 0 && isset($v->id)) {
                            $deductAmount = min($advanceSum, $v->balance);
                            $v->balance -= $deductAmount;
                            $advanceSum -= $deductAmount;
                        } else {
                            break; // Stop if advance is fully utilized
                        }
                    }
                    $data = $invoices;
                } else {
                    $voucher->bill_no = $voucher->reference;
                    $voucher->date = $voucher->voucher->document_date;
                    $voucher->voucher_amount = "-";
                    $voucher->paid = $voucher->orgAmount;
                    $voucher->balance = "-";
                    $data = [$voucher];
                }
            }






            $data = json_decode(json_encode($data));
            $model = $type == 'debit' ? Customer::class : Vendor::class;
            $party = $model::where('ledger_group_id', $group)
                ->where('ledger_id', $ledger)
                ->first();

            if (!$party) {
                throw new Exception("Party (" . ($type == 'debit' ? 'customer' : 'vendor') . ") not found for the selected group and ledger");
            }

            $user = Helper::getAuthenticatedUser();
            $organization = Organization::find($organization_id);

            if (!$organization) {
                throw new Exception("Organization not found.");
            }

            $organizationAddress = Address::with(['city', 'state', 'country'])
                ->where('addressable_id', $user->organization_id)
                ->where('addressable_type', Organization::class)
                ->first();

            if (!$organizationAddress) {
                throw new Exception("Organization address not found.");
            }

            $party_address = ErpAddress::with(['city', 'state', 'country'])
                ->where('addressable_id', $party->id)
                ->where('addressable_type', $model)
                ->first();

            $total_value = array_sum(array_column(array_filter($data, function ($item) {
                return $item->paid > 0;
            }), 'paid'));

            $in_words = Helper::numberToWords($total_value) . " only.";

            $total_value = Helper::formatIndianNumber($total_value);
            $auth_user = Helper::getAuthenticatedUser();
            $orgLogo = Helper::getOrganizationLogo($organization_id);
            $approver = Helper::getDocStatusUser(PaymentVoucher::class, $id, $document->document_status) ?? $document->created_by->name;
            $receipt_no = $document->voucher_no;
            $receipt_date = $document->document_date;
            $remarks = $document->remarks;
            $payment_mode = $document->payment_mode;
            $ref_no = $document->reference_no;
            $payment_type = $document->payment_type;
            $status = $document->document_status;
            $report_type = $document->document_type === "receipts" ? "Received" : "Paid";
            $document_type = $document->document_type === "receipts" ? "Receipt" : "Payment";
            if ($details != null)
                return $data;
            else
                $fileName = $type == "debit" ? str_replace(' ', '_', $ledger_name) . 'Receipt_Advice_' . date('Y-m-d') . '.pdf' : str_replace(' ', '_', $ledger_name) . 'Payment_Advice_' . date('Y-m-d') . '.pdf';
            $pdf = PDF::loadView(
                'pdf.payment',
                [
                    'orgLogo' => $orgLogo,
                    'ledger_name' => $ledger_name,
                    'group_name' => $group_name,
                    'credit_days' => $credit_days,
                    'data' => $data,
                    'ledger' => $ledger,
                    'group' => $group,
                    'type' => $type,
                    'party' => $party,
                    'organization' => $organization,
                    'party_address' => $party_address,
                    'total_value' => $total_value,
                    'in_words' => $in_words,
                    'auth_user' => $auth_user,
                    'organizationAddress' => $organizationAddress,
                    'approver' => $approver,
                    'receipt_no' => $receipt_no, // Fixed this line
                    'receipt_date' => $receipt_date, // Fixed this line
                    'remarks' => $remarks, // Fixed this line
                    'status' => $status, // Fixed this line
                    'payment_mode' => $payment_mode, // Fixed this line
                    'ref_no' => $ref_no, // Fixed this line
                    'payment_type' => $payment_type, // Fixed this line
                    'doc_types' => $doc_types, // Fixed this line
                    'cus_type' => $cus_type, // Fixed this line
                    'report_type' => $report_type, // Fixed this line
                    'document_type' => $document_type // Fixed this line
                ]
            );

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream($fileName);

        } catch (\Throwable $e) {
            Log::error('Payment Print Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if (request()->ajax()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('print_error', $e->getMessage());
        }
    }

    public function sendMail(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'to' => 'required|array',
            'cc' => 'nullable|array',
            'remarks' => 'nullable|string',
            'payment_id' => 'nullable|int',
            'type' => 'nullable|string',
        ]);
        $toIds = $validatedData['to'];
        $f = ucfirst($validatedData['type']);

        foreach ($toIds as $toId) {
            $date = Carbon::now()->format('d-m-Y_His') . '_' . substr(Carbon::now()->format('u'), 0, 3); // 'u' = microseconds, take first 3 digits for milliseconds
            $orgName = Helper::getAuthenticatedUser()?->organization?->name;
            $name = $validatedData['type'] === "receipts" ? "Receipt" : "Payment";
            $dateo = date('d-m-Y');
            $subject = "{$name} advice from {$orgName} | {$dateo}";

            $user = $toId['type']::find($toId['id']);

            if ($user) {
                try {
                    // Generate the report Excel data


                    // Ensure the directory exists
                    if (!Storage::disk('public')->exists('payment-report')) {
                        Storage::disk('public')->makeDirectory('payment-report');
                    }
                    $ledger_name = str_replace(' ', '_', Ledger::find($toId['ledger_id'])?->name);

                    $fileName = "{$ledger_name}_{$f}_{$date}.pdf";

                    $filePath = 'payment-report/' . $fileName;
                    $pdf = self::getPrint($validatedData['payment_id'], $toId['ledger_id'], $toId['ledger_group_id']);

                    // Store the Excel data
                    Storage::disk('public')->put($filePath, $pdf);

                    // Get the URL to the file
                    $fileUrl = Storage::url($filePath);

                    // Log file creation
                    Log::info('Excel file created successfully.', ['file_path' => $filePath]);

                    // Check if file exists before sending email
                    if (!Storage::disk('public')->exists($filePath)) {
                        throw new Exception('File does not exist at path: ' . $filePath);
                    }

                    // Log email building
                    Log::info('Building email for sending report.');

                    $cc_list = $validatedData['cc'];
                    $cc = [];
                    foreach ($cc_list as $cc_l) {
                        $cc[] = AuthUser::find((int) $cc_l)?->email;
                    }

                    // Prepare the MailBox object
                    $mailBox = new MailBox();
                    $mailBox->mail_to = $user->email;
                    $mailBox->mail_cc = implode(',', $cc);
                    $mailBox->mail_body = json_encode([
                        'remarks' => $validatedData['remarks'],
                        'report_type' => $validatedData['type'],
                        'custName' => $user->company_name,
                        'orgName' => $orgName,
                    ]);
                    $mailBox->layout = 'emails.payment_report'; // Ensure you have this layout
                    $mailBox->subject = $subject;
                    // Attach the file with MIME type
                    $mailBox->attachment = env('APP_URL', '/') . $fileUrl;

                    // Use the custom Mailer class to send the email
                    $mailer = new Mailer();
                    $mailer->emailTo($mailBox); // Send email using MailBox object

                    // Log successful sending
                    Log::info('Email sent successfully.');
                } catch (Exception $e) {
                    Log::error('Error in generating or sending the report.', [
                        'error' => $e->getMessage(),
                        'stack' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        return Response::json(['success' => 'Email Send Succesfully!']);
    }
    public function getVoucherBalance($settle, $voucher_id, $doc_type, $ledger, $group, $id = null, $dt = null)
    {
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'type' => $doc_type,
            'partyID' => $ledger,
            'ledgerGroup' => $group,
            'payment_voucher_id' => $id,
            'voucher_id' => $voucher_id,
            'details_id' => $dt
        ]);
        $data = VoucherController::getLedgerVouchers($request);
        $voucher = collect($data->getData()->data)->where('id', $voucher_id)->first();
        $diff = round($voucher->balance, 2) - round($settle, 2);
        return $diff;
    }
    static function getVoucherBalance2($settle, $voucher_id, $doc_type, $ledger, $group, $id = null)
    {

        $type = $doc_type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? 'customer' : 'vendor';



        if ($ledger && $group) {
            $ledger = (int) $ledger;
            $ledger_group = (int) $group;
            $data = Voucher::whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                ->withWhereHas('items', function ($i) use ($ledger, $doc_type, $ledger_group) {
                    $i->where('ledger_id', $ledger)
                        ->where('ledger_parent_id', $ledger_group);

                    if ($doc_type == ConstantHelper::PAYMENTS_SERVICE_ALIAS) {
                        $i->where('credit_amt_org', '>', 0);
                    } else {
                        $i->where('debit_amt_org', '>', 0);
                    }
                })
                ->groupBy('id')  // Assuming 'id' is the primary key or unique field for Voucher
                ->orderBy('document_date', 'asc')
                ->orderBy('created_at', 'asc');



            if (!$id) {

                $data = $data->with([
                    'series' => function ($s) {
                        $s->select('id', 'book_code');
                    }
                ])->select('id', 'amount', 'book_id', 'document_date as date', 'created_at', 'voucher_name', 'voucher_no', 'location', 'organization_id')
                    ->orderBy('id', 'desc')->get()->map(function ($voucher) use ($ledger) {
                        $voucher->date = date('d/m/Y', strtotime($voucher->date));
                        $voucher->document_date = $voucher->document_date;
                        $balance = VoucherReference::where('voucher_id', $voucher->id)
                            ->withWhereHas('voucherPayRec', function ($query) {
                                $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
                                $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                            })->where('party_id', $ledger);

                        $balance = $balance->sum('amount');
                        $voucher->set = $balance;
                        $voucher->balance = $voucher->amount - $balance;



                        return $voucher;
                    });


                $advanceSum = PaymentVoucherDetails::where('type', $type)
                    ->whereIn('reference', ['On Account'])
                    ->withWhereHas('voucher', function ($query) {
                        $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
                        $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                    })
                    ->with('partyName')->get()->filter(function ($adv) use ($ledger, $ledger_group) {
                        if (is_null($adv->ledger_id)) {
                            return $adv->partyName && $adv->partyName->ledger_id == $ledger && $adv->partyName->ledger_group_id == $ledger_group;
                        } else {
                            return $adv->ledger_id == $ledger && $adv->ledger_group_id == $ledger_group;
                        }
                    })->sum('orgAmount');



                foreach ($data as $v) {
                    if ($advanceSum > 0 && isset($v->id)) {
                        $deductAmount = min($advanceSum, $v->balance);
                        $v->balance -= $deductAmount;
                        $advanceSum -= $deductAmount;

                    } else {
                        break; // Stop if advance is fully utilized
                    }
                }
                $advanceItems = PaymentVoucherDetails::where('type', $type)
                    ->where('reference', 'Advance')
                    ->withWhereHas('voucher', function ($query) {
                        $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
                        $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                    })
                    ->with('partyName')->get()->filter(function ($adv) use ($ledger, $ledger_group) {
                        if (is_null($adv->ledger_id)) {
                            return $adv->partyName && $adv->partyName->ledger_id == $ledger && $adv->partyName->ledger_group_id == $ledger_group;
                        } else {
                            return $adv->ledger_id == $ledger && $adv->ledger_group_id == $ledger_group;
                        }
                    });
                foreach ($advanceItems as $advanceItem) {
                    $bucketTotalDeducted = 0;
                    $remainingAdvanceAmount = $advanceItem->orgAmount;

                    // Loop through each customer in the result set
                    foreach ($data as $res) {
                        $documentDate = $advanceItem->voucher->document_date; // e.g. '2025-04-10'
                        $createdAt = $advanceItem->voucher->created_at; // e.g. '2025-04-10 15:30:00'

                        // Combine the date from `document_date` and time from `created_at`
                        $combinedDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $documentDate . ' ' . date('H:i:s', strtotime($createdAt)));

                        $vendorDateTimestamp = $combinedDateTime ? $combinedDateTime->getTimestamp() : null;
                        $resDate = $res->date; // e.g. '10/04/2025'
                        $resCreatedAt = $res->created_at; // e.g. '2025-04-10 14:45:00'

                        // Extract the time from created_at
                        $resTime = date('H:i:s', strtotime($resCreatedAt));

                        // Combine date (converted to Y-m-d) with time
                        $parsedDate = \DateTime::createFromFormat('d/m/Y H:i:s', $resDate . ' ' . $resTime);

                        $resDateTimestamp = $parsedDate ? $parsedDate->getTimestamp() : null;



                        if ($vendorDateTimestamp < $resDateTimestamp) {
                            $bucketTotalDeducted = 0; // Track total amount deducted from all aging buckets
                            if ($remainingAdvanceAmount > 0) {
                                $deductAmount = min($remainingAdvanceAmount, $res->balance);
                                $res->balance -= $deductAmount; // Reduce the bucket value
                                $remainingAdvanceAmount -= $deductAmount; // Reduce the advance sum
                                $bucketTotalDeducted += $deductAmount; // Track total deducted
                            }
                        }

                    }


                }


            } else {
                $data = $data->with([
                    'series' => function ($s) {
                        $s->select('id', 'book_code');
                    }
                ])->select('id', 'amount', 'book_id', 'document_date as date', 'created_at', 'voucher_name', 'voucher_no', 'location', 'organization_id')
                    ->orderBy('id', 'desc')->get()->map(function ($voucher) use ($id, $ledger) {
                        $voucher->date = date('d/m/Y', strtotime($voucher->date));
                        $balance = VoucherReference::where('voucher_id', $voucher->id)
                            ->withWhereHas('voucherPayRec', function ($query) use ($id) {
                                $query->where('payment_voucher_id', '!=', (int) $id);
                                $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
                                $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                            })->where('party_id', $ledger)->sum('amount');

                        $settle = VoucherReference::where('voucher_id', $voucher->id)
                            ->where('payment_voucher_id', (int) $id)
                            ->where('party_id', $ledger)->sum('amount');

                        $voucher->balance = (float) $voucher->amount - (float) $balance;
                        $voucher->settle = $settle;


                        return $voucher;
                    });


                $advanceSum = PaymentVoucherDetails::where('type', $type)
                    ->whereIn('reference', ['On Account'])
                    ->withWhereHas('voucher', function ($query) {
                        $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id)
                            ->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                    })
                    ->with('partyName')->get()->filter(function ($adv) use ($ledger, $ledger_group) {
                        if (is_null($adv->ledger_id)) {
                            return $adv->partyName && $adv->partyName->ledger_id == $ledger && $adv->partyName->ledger_group_id == $ledger_group;
                        } else {
                            return $adv->ledger_id == $ledger && $adv->ledger_group_id == $ledger_group;
                        }
                    })->sum('orgAmount');






                foreach ($data as $v) {
                    if ($advanceSum > 0 && isset($v->id)) {
                        $deductAmount = min($advanceSum, $v->balance);
                        $v->balance -= $deductAmount;
                        $advanceSum -= $deductAmount;

                    } else {
                        break; // Stop if advance is fully utilized
                    }
                }
                $advanceItems = PaymentVoucherDetails::where('type', $type)
                    ->whereIn('reference', ['Advance'])
                    ->withWhereHas('voucher', function ($query) {
                        $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id)
                            ->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                    })
                    ->with('partyName')->get()->filter(function ($adv) use ($ledger, $ledger_group) {
                        if (is_null($adv->ledger_id)) {
                            return $adv->partyName && $adv->partyName->ledger_id == $ledger && $adv->partyName->ledger_group_id == $ledger_group;
                        } else {
                            return $adv->ledger_id == $ledger && $adv->ledger_group_id == $ledger_group;
                        }
                    });
                foreach ($advanceItems as $advanceItem) {
                    $bucketTotalDeducted = 0;
                    $remainingAdvanceAmount = $advanceItem->orgAmount;

                    // Loop through each customer in the result set
                    foreach ($data as $res) {
                        $documentDate = $advanceItem->voucher->document_date; // e.g. '2025-04-10'
                        $createdAt = $advanceItem->voucher->created_at; // e.g. '2025-04-10 15:30:00'

                        // Combine the date from `document_date` and time from `created_at`
                        $combinedDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $documentDate . ' ' . date('H:i:s', strtotime($createdAt)));

                        $vendorDateTimestamp = $combinedDateTime ? $combinedDateTime->getTimestamp() : null;
                        $resDate = $res->date; // e.g. '10/04/2025'
                        $resCreatedAt = $res->created_at; // e.g. '2025-04-10 14:45:00'

                        // Extract the time from created_at
                        $resTime = date('H:i:s', strtotime($resCreatedAt));

                        // Combine date (converted to Y-m-d) with time
                        $parsedDate = \DateTime::createFromFormat('d/m/Y H:i:s', $resDate . ' ' . $resTime);

                        $resDateTimestamp = $parsedDate ? $parsedDate->getTimestamp() : null;


                        if ($vendorDateTimestamp < $resDateTimestamp) {
                            $bucketTotalDeducted = 0; // Track total amount deducted from all aging buckets
                            if ($remainingAdvanceAmount > 0) {
                                $deductAmount = min($remainingAdvanceAmount, $res->balance);
                                $res->balance -= $deductAmount; // Reduce the bucket value
                                $remainingAdvanceAmount -= $deductAmount; // Reduce the advance sum
                                $bucketTotalDeducted += $deductAmount; // Track total deducted
                            }
                        }

                    }


                }




            }

            return $data->filter(fn($item) => $item->id == $voucher_id)
                ->pluck('balance')
                ->first() - $settle;
        } else {
            return response()->json(['data' => [], 'ledgerId' => null]);
        }







        //     if($id){
        //         $voucher = Voucher::find($voucher_id);
        //     if ($voucher) {
        //         $settle = VoucherReference::where('voucher_id', $voucher->id)
        //         ->where('payment_voucher_id', '!=', $id)
        //             ->withWhereHas('voucherPayRec', function ($query) use($doc_type) {
        //                 $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
        //                 $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
        //                 $query->where('document_type',$doc_type);
        //             })->sum('amount');


        //           $balance =   (int)$voucher->amount - $settle;
        //         return $balance;
        //     } else {
        //         return 0;
        //     }
        //     }
        //     else{
        //     $voucher = Voucher::find($voucher_id);
        //     if ($voucher) {
        //         $settle = VoucherReference::where('voucher_id', $voucher->id)
        //             ->withWhereHas('voucherPayRec', function ($query) use($doc_type) {
        //                 $query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
        //                 $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
        //                 $query->where('document_type',$doc_type);
        //             })->sum('amount');
        //           $balance =   (int)$voucher->amount - $settle;
        //         return $balance;
        //     } else {
        //         return 0;
        //     }
        // }
    }
    public function checkReference(Request $request)
    {
        $unique = count($request->otherRefs) === count(array_unique($request->otherRefs));
        if(!$unique)
            return response()->json(['exists' => true]);
        
        
        if ($request->edit_id){
            if (in_array($request->reference_no, $request->otherRefs ?? [])) {
            return response()->json(['exists' => true]);
            }
            $exists = PaymentVoucherDetails::where('reference_no', $request->reference_no)
                ->where('payment_voucher_id', '!=', $request->edit_id)->exists();
            }
        else
            $exists = PaymentVoucherDetails::where('reference_no', $request->reference_no)->exists();

        return response()->json(['exists' => $exists]);
    }
}
