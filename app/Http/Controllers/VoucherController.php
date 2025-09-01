<?php

namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\Validator;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use App\Models\ErpService;
use App\Models\Book;
use App\Models\BookType;
use App\Models\CostGroup;
use Illuminate\Http\Request;
use App\Helpers\InventoryHelper;
use App\Models\CostCenter;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApiGenericException;
use App\Models\ApprovalWorkflow;
use App\Models\VoucherReference;
use App\Models\CostCenterOrgLocations;
use App\Models\Voucher;
use App\Models\Ledger;
use App\Models\ItemDetail;
use App\Models\NumberPattern;
use App\Models\Organization;
use Auth;
use App\Helpers\Helper;
use App\Models\DocumentApproval;
use App\Models\VoucherHistory;
use Carbon\Carbon;
use Exception;
use App\Models\ErpStore;
use Hamcrest\Arrays\IsArray;

use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Models\OrganizationService;
use App\Models\PaymentVoucherDetails;
use App\Models\Group;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Vendor;



class VoucherController extends Controller
{
    public static function getLedgerVouchers(Request $request)
    {
        $type = $request->type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? 'customer' : 'vendor';



        if ($request->partyID && $request->ledgerGroup) {
            $ledger = (int) $request->partyID;
            $accessibleLocations = collect(InventoryHelper::getAccessibleLocations());
            $locationIds = $accessibleLocations->pluck('id')->all();
            $ledger_group = (int) $request->ledgerGroup;
            $user = Helper::getAuthenticatedUser();
            if ($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS) 
            {
            $orgsFromAccess = collect(optional($user)->access_rights_org)->pluck('organization_id');
            $orgs = $orgsFromAccess->isEmpty()
                ? [optional($user)->organization_id]
                : $orgsFromAccess->all();
           } 
           else 
           {
            $orgs = [optional($user)->organization_id];
          }


            $data = Voucher::when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
            })->whereIn("organization_id",$orgs)
                ->with([ 'ErpLocation' => function ($query) use ($request, $orgs) {
                 $query->when(function () use ($request) {
            return $request->type === ConstantHelper::PAYMENTS_SERVICE_ALIAS;
        }, function ($q) {
            $q->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');;
        })->whereIn('organization_id', $orgs);
            }])
            ->with('organization')
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                //->whereIn('location', $locationIds)
                ->withWhereHas('items', function ($i) use ($ledger, $request, $ledger_group) {
                    $i->where('ledger_id', $ledger)
                    ->where('ledger_parent_id', $ledger_group);

                    if ($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS) {
                        $i->where('credit_amt_org', '>', 0);
                    } else {
                        $i->where('debit_amt_org', '>', 0);
                    }
                    $i->with([
                    'ledger.organization', 
                    'ledger.vendor', 
                    'ledger.customer',
                    'ledger_group',
                    'costCenter',
                    ]);
                })
                ->groupBy('id')  // Assuming 'id' is the primary key or unique field for Voucher
                ->orderBy('document_date', 'asc')
                ->orderBy('created_at', 'asc');


                if ($request->filled('date')) {
                    [$startDate, $endDate] = explode(' to ', $request->date);

                    $start = Carbon::parse(trim($startDate))->format('Y-m-d');
                    $end = Carbon::parse(trim($endDate))->format('Y-m-d');

                    $data->whereBetween('document_date', [$start, $end]);
                }


            if ($request->book_code) {
                $data = $data->whereHas('series', function ($q) use ($request) {
                    $q->whereHas('org_service', function ($subQuery) use ($request) {
                        $subQuery->where('alias', $request->book_code);
                    });
                });
            }

            if ($request->document_no) {
                $data = $data->where('voucher_no', 'like', "%" . $request->document_no . "%");
            }


            if (!$request->payment_voucher_id) {

                $data = $data->with(['series' => function ($s) use($request,$orgs) {
                    $s->select('id', 'book_code');
                }])->select('id', 'amount', 'book_id', 'document_date as date','created_at', 'voucher_name', 'voucher_no', 'location', 'organization_id')
                    ->orderBy('id', 'desc')->get()->map(function ($voucher) use ($request, $ledger,$orgs) {
                        $voucher->date = date('d/m/Y', strtotime($voucher->date));
                        $voucher->document_date = $voucher->document_date;
                        $balance = VoucherReference::where('voucher_id', $voucher->id)
                            ->withWhereHas('voucherPayRec', function ($query) use($request,$orgs) {
                                $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                                    $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                                    ->withoutGlobalScope('defaultLocation');
                                })->whereIn("organization_id",$orgs);
                                //$query->where('organization_id', Helper::getAuthenticatedUser()->organization_id);
                                $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                            })->where('party_id', $ledger);
                        $amount = 0;
                        foreach ($voucher->items as $item) {
                            $amount += $request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS ? $item->credit_amt_org : $item->debit_amt_org;
                        }
                        $voucher->amount = $amount;
               
                        $balance = $balance->sum('amount');
                        $voucher->set = $balance;
                        $voucher->balance = $voucher->amount - $balance;



                        return $voucher;
                    });


                    $advanceSum = PaymentVoucherDetails::where('type', $type)
                    ->whereIn('reference', ['On Account'])
                    ->withWhereHas('voucher', function ($query) use($orgs,$request) {
                        $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                        $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
                    })->whereIn("organization_id",$orgs);
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
                 ->withWhereHas('voucher', function ($query) use($request,$orgs) {
                     $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
            })->whereIn("organization_id",$orgs);
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
                if ($request->details_id != null && $request->page=="view") {
                    $data = $data->with(['series' => function ($s)  {
                        $s->select('id', 'book_code');
                    }])->select('id', 'amount', 'book_id', 'document_date as date','created_at', 'voucher_name', 'voucher_no', 'location', 'organization_id')
                        ->orderBy('id', 'desc')->get()->map(function ($voucher) use ($request, $ledger,$orgs) {
                            $voucher->date = date('d/m/Y', strtotime($voucher->date));
                            $settle = VoucherReference::where('voucher_id', $voucher->id)
                                ->where('payment_voucher_id', (int)$request->payment_voucher_id)
                                ->where('voucher_details_id', (int)$request->details_id)
                                ->where('party_id', $ledger)->sum('amount');

                                $balance = VoucherReference::where('payment_voucher_id','<',(int)$request->payment_voucher_id)
                                ->where('voucher_id', $voucher->id)
                                ->withWhereHas('voucherPayRec', function ($query) use($request,$orgs) {
                                    $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
            })->whereIn("organization_id",$orgs);
            $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                                })->where('party_id', $ledger)->sum('amount');
                                $amount = 0;
                                foreach ($voucher->items as $item) {
                                    $amount += $request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS ? $item->credit_amt_org : $item->debit_amt_org;
                                }
                                $voucher->amount = $amount;

                                $voucher->settle = $settle;
                                $voucher->balance = $voucher->amount -$balance;

                            return $voucher;
                        });
                        $advanceSum = PaymentVoucherDetails::where('type', $type)
                        ->where('payment_voucher_id','<',(int)$request->payment_voucher_id)
                        ->whereIn('reference', ['On Account'])
                        ->withWhereHas('voucher', function ($query) use($orgs,$request) {
                            $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
            })->whereIn("organization_id",$orgs)
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
                    ->where('payment_voucher_id','<',(int)$request->payment_voucher_id)
                    ->whereIn('reference', ['Advance'])
                    ->withWhereHas('voucher', function ($query) use($request,$orgs) {
                        $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                            $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
                        })->whereIn("organization_id",$orgs)
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


                } else {
                    $data = $data->with(['series' => function ($s) {
                        $s->select('id', 'book_code');
                    }])->select('id', 'amount', 'book_id', 'document_date as date','created_at', 'voucher_name', 'voucher_no','location', 'organization_id')
                        ->orderBy('id', 'desc')->get()->map(function ($voucher) use ($request, $ledger,$orgs) {
                            $voucher->date = date('d/m/Y', strtotime($voucher->date));
                             $amount = 0;
                                foreach ($voucher->items as $item) {
                                    $amount += $request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS ? $item->credit_amt_org : $item->debit_amt_org;
                                }
                                $voucher->amount = $amount;
                            $balance = VoucherReference::where('voucher_id', $voucher->id)
                                ->withWhereHas('voucherPayRec', function ($query) use ($request,$orgs) {
                                    $query->where('payment_voucher_id','!=',(int)$request->payment_voucher_id);
                                    $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
            })->whereIn("organization_id",$orgs);
            $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                                })->where('party_id', $ledger)->sum('amount');

                                $settle = VoucherReference::where('voucher_id', $voucher->id)
                                ->where('payment_voucher_id', (int)$request->payment_voucher_id)
                                ->where('voucher_details_id', (int)$request->details_id)
                                ->where('party_id', $ledger)->sum('amount');

                            $voucher->balance = $voucher->amount-$balance;
                            $voucher->settle = $settle;


                            return $voucher;
                        });


                        $advanceSum = PaymentVoucherDetails::where('type', $type)
                        ->whereIn('reference', ['On Account'])
                        ->withWhereHas('voucher', function ($query) use($request,$orgs) {
                            $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
            })->whereIn("organization_id",$orgs)
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
                    ->withWhereHas('voucher', function ($query) use($request,$orgs) {
                        $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
            })->whereIn("organization_id",$orgs)
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

            }

            return response()->json(['data' => $data, 'ledgerId' => $ledger,'sum'=>$advanceSum]);
        } else {
            return response()->json(['data' => [], 'ledgerId' => null]);
        }
    }

    public function amendment(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $voucher = Voucher::find($id);
            if (!$voucher) {
                return response()->json(['data' => [], 'message' => "Payment Voucher not found.", 'status' => 404]);
            }

            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'Voucher', 'relation_column' => ''],
                ['model_type' => 'detail', 'model_name' => 'ItemDetail', 'relation_column' => 'voucher_id']
            ];

            $a = Helper::documentAmendment($revisionData, $id);
            if ($a) {
                Helper::approveDocument($voucher->book_id, $voucher->id, $voucher->revision_number, 'Amendment', $request->file('attachment') ?? null, $voucher->approvalLevel, 'amendment');

                $voucher->approvalStatus = ConstantHelper::DRAFT;
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

    public function approveVoucher(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $saleOrder = Voucher::find($request->id);
            $bookId = $saleOrder->book_id;
            $docId = $saleOrder->id;
            $docValue = $saleOrder->amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
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

    public function ledgers_search(Request $r)
    {
        $book = $r->series;
        $book = OrganizationService::where('id', $book)->first();
        $book = $book?->alias;


        if ($book == ConstantHelper::CONTRA_VOUCHER) {
            $allowedNames = ConstantHelper::CV_ALLOWED_GROUPS;
            $allChildIds = Helper::getChildLedgerGroupsByNameArray($allowedNames);

            $data = Ledger::where('status', 1)
                ->where('name', 'like', '%' . $r->keyword . '%')
                ->where(function ($query) use ($allChildIds) {
                    // First: match plain integer values
                    $query->whereIn('ledger_group_id', $allChildIds)
                        ->orWhere(function ($subQuery) use ($allChildIds) {
                            // Then: check each allowed ID against JSON array type
                            $i = 0;
                            $count = count($allChildIds);

                            while ($i < $count) {
                                $child = (string)$allChildIds[$i];
                                if ($i === 0) {
                                    $subQuery->whereJsonContains('ledger_group_id',$child);
                                } else {
                                    $subQuery->orWhereJsonContains('ledger_group_id',$child)->orWhereJsonContains('ledger_group_id',$child);
                                }
                                $i++;
                            }
                        });
                });
        }

        else if ($book == ConstantHelper::JOURNAL_VOUCHER) {
            $excludeNames = ConstantHelper::JV_EXCLUDE_GROUPS;
            $allChildIds = Helper::getChildLedgerGroupsByNameArray($excludeNames);

            $data = Ledger::where('status', 1)
                ->where('name', 'like', '%' . $r->keyword . '%')
                    // Exclude plain integer match
                ->whereNotIn('ledger_group_id', $allChildIds)
                    // Exclude JSON array match
                ->where(function ($query) use ($allChildIds) {
                    $i = 0;
                    $count = count($allChildIds);

                    while ($i < $count) {
                        $child = (string)$allChildIds[$i];

                        $query->whereJsonDoesntContain('ledger_group_id', $child);
                        $i++;
                    }
                });
        }

        else {
            $data = Ledger::where('status', 1)
                ->where('name', 'like', '%' . $r->keyword . '%');
        }

        $data = $data->select('id as value', 'name as label', 'cost_center_id')->get()->toArray();
        return response()->json($data);
     }

    public function get_voucher_no($book_id)
    {
        $data = Helper::generateVoucherNumber($book_id);
        return response()->json($data);
    }

    public function index(Request $request)
    {
       
        $parentURL = request()->segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }



        $user = Helper::getAuthenticatedUser();
        $userId = $user->id;
        $organizationId = $user->organization_id;

        $organizations = [];
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


        // Retrieve vouchers based on organization_id and include series with levels
        $cost_center_ids = null;
        if (!empty($request->cost_center_id)) {
            $cost_center_ids = $request->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($request->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $request->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
                        // dd($cost_center_ids);
        }
        $data =  Voucher::with([
            'documents:id,name',
        ])
        ->whereHas('items', function ($d) use ($cost_center_ids) {
           if (!empty($cost_center_ids)) {
            // dd($cost_center_ids);
            if (is_array($cost_center_ids)) {
                $d->whereIn('cost_center_id', $cost_center_ids);
            } else {
                $d->where('cost_center_id', $cost_center_ids);
            }
        }
        })
        ->where('approvalStatus', '!=', 'cancel')
        ->whereIn('location', $locationIds);
        // Apply filters based on the request
        if ($request->book_type) {
            $data = $data->where('book_type_id', $request->book_type);
        }

        if ($request->location_id) {
            $data = $data->where('location', $request->location_id);
        }

        if ($request->voucher_no) {
            $data = $data->where('voucher_no', 'like', "%" . $request->voucher_no . "%");
        }

        if ($request->voucher_name) {
            $data = $data->where('voucher_name', 'like', "%" .  $request->voucher_name . "%");
        }

        if ($request->date) {
            $dates = explode(' to ', $request->date);

            // If no end date, use start date as end date
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $start;

            $data = $data->whereDate('document_date', '>=', $start)
                        ->whereDate('document_date', '<=', $end);
        }
        else{

            $data = $data->whereDate('document_date', '>=',$fyear['start_date'])
                ->whereDate('document_date', '<=',$fyear['end_date']);
                $start = $fyear['start_date'];
                $end = $fyear['end_date'];


        }


        $data = $data->orderBy('document_date', 'desc')->get();

        $parentUrl = request()->segments()[0];

        $serviceAlias = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($serviceAlias['services']) == 0) {
            return redirect()->route('/');
        }

        $bookTypes = $serviceAlias['services'];

        $mappings =Helper::access_org();

        $book_type = $request->book_type;
        $date = $request->date ?? \Carbon\Carbon::parse($fyear['start_date'])->format('d-m-Y') . " to " . \Carbon\Carbon::parse($fyear['end_date'])->format('d-m-Y');
        $date2 = \Carbon\Carbon::parse($start)->format('jS-F-Y') . ' to ' . \Carbon\Carbon::parse($end)->format('jS-F-Y');

        $voucher_no = $request->voucher_no;
        $voucher_name = $request->voucher_name;
        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status','active')->get()->toArray();
         $fyearLocked = $fyear['authorized'];
        $locations = InventoryHelper::getAccessibleLocations();
        return view('voucher.view_vouchers', compact('cost_centers','bookTypes', 'mappings', 'organizationId', 'data', 'book_type', 'date', 'voucher_no', 'voucher_name','date2','fyearLocked','locations','cost_groups'));
    }

    public function create()
    {
        $cost_centers = CostCenter::where('status', 'active')->where('organization_id', Helper::getAuthenticatedUser()->organization_id)->select('id as value', 'name as label')->get()->toArray();
        // $serviceAlias = [ConstantHelper::PURCHASE_VOUCHER, ConstantHelper::SALES_VOUCHER, ConstantHelper::RECEIPT_VOUCHER, ConstantHelper::PAYMENT_VOUCHER, ConstantHelper::DEBIT_Note, ConstantHelper::CREDIT_Note, ConstantHelper::JOURNAL_VOUCHER, ConstantHelper::CONTRA_VOUCHER];
        $parentUrl = request()->segments()[0];

        $serviceAlias = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $fy_months = Helper::getCurrentFinancialYearMonths();
      


        $bookTypes = $serviceAlias['services'];

        $bookTypes = collect($bookTypes)
            ->whereIn('alias', [
                ConstantHelper::CONTRA_VOUCHER,
                ConstantHelper::JOURNAL_VOUCHER,
                ConstantHelper::OPENING_BALANCE
            ])
            ->unique('alias')  
            ->values() ?? [];

        // $bookTypes = collect($bookTypes)->whereIn('alias', [ConstantHelper::CONTRA_VOUCHER,ConstantHelper::JOURNAL_VOUCHER,ConstantHelper::OPENING_BALANCE])->values()??[];
       


        

        $lastVoucher = Voucher::where('organization_id', Helper::getAuthenticatedUser()->organization_id)->orderBy('id', 'desc')->select('book_type_id', 'book_id')->first();
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'name', 'short_name')->get();
        $orgCurrency = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->value('currency_id');
        $allledgers = Ledger::get();
        $allowedCVGroups = Helper::getChildLedgerGroupsByNameArray(ConstantHelper::CV_ALLOWED_GROUPS,'names');
        $exlucdeJVGroups = Helper::getChildLedgerGroupsByNameArray(ConstantHelper::JV_EXCLUDE_GROUPS,'names');
        $cost_centers = Helper::getActiveCostCenters();
        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        // pass authenticate user's org locations
     $locations = InventoryHelper::getAccessibleLocations();
         return view('voucher.create_voucher', compact('cost_centers','allledgers', 'currencies', 'orgCurrency', 'cost_centers', 'bookTypes', 'lastVoucher','allowedCVGroups','exlucdeJVGroups','locations','fyear','fy_months'));
    }

    function get_series($id)
    {
        // $parentURL = request() -> segments()[0];
        $service = OrganizationService::find($id);
        return response()->json(Helper::getBookSeriesNew($service->alias, "vouchers")->get());
    }
    public function edit(Request $r, $id)
    {
        $currNumber = $r->revisionNumber;
        $data = Voucher::with(['items','ErpLocation'])->find($id);
        if ($r->has('revisionNumber') && $data->revision_number!=$currNumber) {
            $data = VoucherHistory::where('source_id', $id)->where('revision_number', $currNumber)->first();
        } else {
            $data = Voucher::with(['items','ErpLocation'])->find($id);
        }
        $parentUrl = request()->segments()[0];
        $cost_centers = CostCenter::where('organization_id', Helper::getAuthenticatedUser()->organization_id)->select('id as value', 'name as label')->get()->toArray();
        $serviceAlias = [ConstantHelper::PURCHASE_VOUCHER, ConstantHelper::SALES_VOUCHER, ConstantHelper::RECEIPT_VOUCHER, ConstantHelper::PAYMENT_VOUCHER, ConstantHelper::DEBIT_Note, ConstantHelper::CREDIT_Note, ConstantHelper::JOURNAL_VOUCHER, ConstantHelper::CONTRA_VOUCHER];
        // $bookTypes = OrganizationService::whereIn('alias', $serviceAlias)->where('status', ConstantHelper::ACTIVE)->get();
        $serviceAlias = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($serviceAlias['services']) == 0) {
            return redirect()->route('/');
        }


        $bookTypes = $serviceAlias['services'];
        $books = [];
        // Loop through each alias and collect the series
        foreach ($bookTypes as $alias) {
            $books[] = Helper::getBookSeriesNew($alias->alias, $parentUrl)->get(); // Keep the structure as it is
        }
        //dd($books);

        //$books = Book::where('booktype_id', $data->book_type_id)->select('id', 'book_code as code', 'book_name')->get();
        $creatorType = explode("\\", $data->voucherable_type);

        $buttons = Helper::actionButtonDisplay($data->book_id, $data->approvalStatus, $data->id, $data->amount, $data->approvalLevel, $data->voucherable_id, strtolower(end($creatorType)));
        $buttons['reference']=false;
        if ($data->approvalStatus === ConstantHelper::DRAFT || $data->approvalStatus === ConstantHelper::SUBMITTED)
            $buttons['cancel'] = true;
        else
            $buttons['cancel'] = false;
        $revision_number = $data->revision_number;
        $revNo = $data->revision_number;
        if ($r->has('revisionNumber')) {
            $revNo = intval($r->revisionNumber);
        } else {
            $revNo = $data->revision_number;
        }
        $ref_view_route="#";

                $history = Helper::getApprovalHistory($data->book_id, $id, $revNo,$data->amount,$data->created_by);
            $revisionNumbers = $history->pluck('revision_number')->unique()->values()->all();

        if ($data->reference_doc_id && $data->reference_service) {
            $model = Helper::getModelFromServiceAlias($data->reference_service);
            if ($model != null) {
                $referenceDoc = $model::find($data->reference_doc_id);
                if ($referenceDoc != null) {
                    $history = Helper::getApprovalHistory($referenceDoc->book_id, $referenceDoc->id, $referenceDoc->revision_number,0,$referenceDoc->created_by);
                    $ref_view_route = Helper::getRouteNameFromServiceAlias($data->reference_service,$data->reference_doc_id);
                    $buttons['reference']=true;
                }
            }
             $buttons['amend']=false;
        }
        $currencies = Currency::where('status', ConstantHelper::ACTIVE)->select('id', 'name', 'short_name')->get();
        $orgCurrency = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->value('currency_id');
        $groups = Group::all();
        $approvalHistory = $history;
        $allowedCVGroups = Helper::getChildLedgerGroupsByNameArray(ConstantHelper::CV_ALLOWED_GROUPS,'names');
        $exlucdeJVGroups = Helper::getChildLedgerGroupsByNameArray(ConstantHelper::JV_EXCLUDE_GROUPS,'names');
        $cost_centers = Helper::getActiveCostCenters();
        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        $locations = InventoryHelper::getAccessibleLocations();
       return view('voucher.edit_voucher', compact('cost_centers','groups', 'orgCurrency', 'currencies', 'cost_centers', 'bookTypes', 'data', 'books', 'buttons', 'history', 'revision_number', 'currNumber','approvalHistory','ref_view_route','allowedCVGroups','exlucdeJVGroups','locations','fyear'));
    }

    public function store(Request $request)
    {
        $numberPatternData = Helper::generateDocumentNumberNew($request -> book_id, $request -> date);
                if (!isset($numberPatternData)) {
                    return response()->json([
                        'message' => "Invalid Book",
                        'error' => "",
                    ], 422);
                }


        $voucherExists = Voucher::where('voucher_no', $numberPatternData['document_number'])
        ->where('book_id',$request -> book_id)->exists();

        if ($voucherExists) {
            return redirect()
                ->route('vouchers.create')
                ->withErrors(['voucher_no' => $request->voucher_no . ' Voucher No. Already Exist!']);
        }


        $validator = Validator::make($request->all(), [
            'voucher_name' => 'required|string',
            'date' => 'required|date',
            'document' => 'nullable|array',
            'document.*' => 'file',
            'debit_amt' => 'required|array',
            'credit_amt' => 'required|array',
            'voucher_no' => 'required|string',
            'ledger_id' => 'required|array',
            'ledger_id.*' => 'required|numeric|min:1',
            'parent_ledger_id' => 'required|array',
            'location' => 'required|numeric|min:1',
            'parent_ledger_id.*' => 'required|numeric|min:1',  //parent_ledger_id
        ], [
            // Custom error messages
            'voucher_name.required' => 'The voucher name is required.',
            'voucher_name.string' => 'The voucher name must be a string.',
            'date.required' => 'The date is required.',
            'date.date' => 'The date must be a valid date format.',
            'document.array' => 'The document field must be an array.',
            'document.*.file' => 'Each document must be a valid file.',
            'debit_amt.required' => 'The debit amount is required.',
            'debit_amt.array' => 'The debit amount must be an array.',
            'credit_amt.required' => 'The credit amount is required.',
            'credit_amt.array' => 'The credit amount must be an array.',
            'voucher_no.required' => 'The voucher number is required.',
            'voucher_no.string' => 'The voucher number must be a string.',
            'ledger_id.required' => 'The ledger ID field is required.',
            'ledger_id.array' => 'The ledger ID must be an array.',
            'ledger_id.*.required' => 'Each ledger ID is required.',
            'parent_ledger_id.array' => 'The ledger Group ID must be an array.',
            'parent_ledger_id.*.required' => 'Each ledger Group is required.',
            'parent_ledger_id.required' => 'Ledger Group is required.',
            'location.required' => 'Location is required.',


        ]);


        if ($validator->fails()) {
            return redirect()
                ->route('vouchers.create')
                ->withInput() // Pass the input data back to the session
                ->withErrors($validator); // Pass the validation errors back to the session
        }

        // Continue with logic if validation passes

        $voucher_no = $request->voucher_no;

        $organization = Helper::getAuthenticatedUser()->organization;

        // Create a new voucher
        $voucher = new Voucher();
        $voucher->voucher_no = $numberPatternData['document_number'];
        $voucher->voucher_name = $request->voucher_name;
        $voucher->book_type_id = $request->book_type_id;
        $voucher->book_id = $request->book_id;

        //currency_related_fileds
        $voucher->currency_id = $request->currency_id;
        $voucher->currency_code = $request->currency_code;
        $voucher->org_currency_exg_rate = $request->orgExchangeRate;
        $voucher->org_currency_id = $request->org_currency_id;
        $voucher->org_currency_code = $request->org_currency_code;
        $voucher->org_currency_exg_rate = $request->org_currency_exg_rate;
        $voucher->comp_currency_id = $request->comp_currency_id;
        $voucher->comp_currency_code = $request->comp_currency_code;
        $voucher->comp_currency_exg_rate = $request->comp_currency_exg_rate;
        $voucher->group_currency_id = $request->group_currency_id;
        $voucher->group_currency_code = $request->group_currency_code;
        $voucher->group_currency_exg_rate = $request->group_currency_exg_rate;

        $voucher->date = $request->date;
        $voucher->remarks = $request->remarks;
        $voucher->amount = $request->amount;
        $voucher->organization_id = $organization->id;
        $voucher->group_id = $organization->group_id;
        $voucher->company_id = $organization->company_id;
        $voucher->revision_number = 0;

        $voucher->document_date = $request->date;
        $voucher->date = $request->date;
        $voucher->location = $request->location;
        $voucher->doc_no = $numberPatternData['doc_no'];
        $voucher->doc_number_type = $numberPatternData['type'];
        $voucher->doc_reset_pattern = $numberPatternData['reset_pattern'];
        $voucher->doc_prefix = $numberPatternData['prefix'];
        $voucher->doc_suffix = $numberPatternData['suffix'];
        $voucher->approvalStatus = $request->status;
        $voucher->created_by = Helper::getAuthenticatedUser()->auth_user_id;
        $voucher->approvalLevel = 1;

        


        if ($request->hasFile('document')) {
            $files = $request->file('document'); // 'document' should be an array of files
            $fileNames = [];

            foreach ($files as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('voucherDocuments');
                $file->move($destinationPath, $fileName);

                // Store the file name in an array (you can save it in the database if needed)
                $fileNames[] = $fileName;
            }

            // If you want to save multiple filenames in the database
            $voucher->document = json_encode($fileNames); // Save file names as a JSON string
        }
        $userData = Helper::userCheck();
        $voucher->voucherable_id = Helper::getAuthenticatedUser()->auth_user_id;
        $voucher->voucherable_type = $userData['user_type'];

        $voucher->save();
        
        $voucherId = $voucher->id;
        if ($request->status == ConstantHelper::SUBMITTED) {
                        $bookId = $voucher->book_id;
                        $docId = $voucher->id;
                        $remarks = $voucher->remarks;
                        $attachments = $request->file('attachment');
                        $currentLevel = $voucher->approval_level;
                        $revisionNumber = $voucher->revision_number ?? 0;
                        $actionType = 'submit'; // Approve // reject // submit
                        $modelName = get_class($voucher);
                        $totalValue = $voucher->amount ?? 0;
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                        $voucher->approvalStatus = $approveDocument['approvalStatus'] ?? $voucher->document_status;
                        $voucher->save();

        }
    

        // Process item details
        $debitAmts = $request->input('debit_amt');
        $creditAmts = $request->input('credit_amt');

        $debitAmtsComp = $request->input('comp_debit_amt');
        $creditAmtsComp = $request->input('comp_credit_amt');

        $debitAmtsOrg = $request->input('org_debit_amt');
        $creditAmtsOrg = $request->input('org_credit_amt');

        $itemRemarks = $request->input('item_remarks');


        $debitAmtsGroup = $request->input('group_debit_amt');
        $costCenters = $request->input('cost_center_id');
        $creditAmtsGroup = $request->input('group_credit_amt');

        $parentLedger = $request->input('parent_ledger_id');



        foreach ($debitAmts as $index => $debitAmount) {
            if (isset($request->ledger_id[$index]) && isset($parentLedger[$index])) {

                $notename = "notes" . ($index + 1);

                $debit = $debitAmts[$index] ?? 0;
                $credit = $creditAmts[$index] ?? 0;

                $debitComp = $debitAmtsComp[$index] ?? 0;
                $creditComp = $creditAmtsComp[$index] ?? 0;
                $cost_center_id = $costCenters[$index]??null;

                $debitGroup = $debitAmtsGroup[$index] ?? 0;
                $creditGroup = $creditAmtsGroup[$index] ?? 0;

                $debitOrg = $debitAmtsOrg[$index] ?? 0;
                $creditOrg = $creditAmtsOrg[$index] ?? 0;

                $ledger_id = $request->ledger_id[$index];
                $parent_ledger_id = $parentLedger[$index];

                $item_remarks = $itemRemarks[$index] ?? "";

                // Insert the new ItemDetail record
                ItemDetail::create([
                    'voucher_id' => $voucherId,
                    'ledger_id' => $ledger_id,
                    'debit_amt' => $debit,
                    'credit_amt' => $credit,
                    'debit_amt_org' => $debitOrg,
                    'credit_amt_org' => $creditOrg,
                    'debit_amt_comp' => $debitComp,
                    'credit_amt_comp' => $creditComp,
                    'debit_amt_group' => $debitGroup,
                    'credit_amt_group' => $creditGroup,
                    'ledger_parent_id' => $parent_ledger_id,
                    'cost_center_id' => $cost_center_id,
                    'notes' => $request->$notename,
                    'date' => $request->date,
                    'organization_id' => $organization->id,
                    'group_id' => $organization->group_id,
                    'company_id' => $organization->group_id,
                    'remarks' => $item_remarks
                ]);
            }
        }

        return redirect()->route("vouchers.index")->with('success', 'Voucher created successfully.');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'document' => 'nullable|array',
            'document.*' => 'file',
            'debit_amt' => 'required|array',
            'credit_amt' => 'required|array',
            'voucher_no' => 'required|string',
            'ledger_id' => 'required|array',
            'ledger_id.*' => 'required|numeric|min:1',
            'parent_ledger_id' => 'required|array',
            'location' => 'required|numeric|min:1',
            'parent_ledger_id.*' => 'required|numeric|min:1',  //parent_ledger_id
        ], [
            // Custom error messages
            'date.required' => 'The date is required.',
            'date.date' => 'The date must be a valid date format.',
            'document.array' => 'The document field must be an array.',
            'document.*.file' => 'Each document must be a valid file.',
            'debit_amt.required' => 'The debit amount is required.',
            'debit_amt.array' => 'The debit amount must be an array.',
            'credit_amt.required' => 'The credit amount is required.',
            'credit_amt.array' => 'The credit amount must be an array.',
            'voucher_no.required' => 'The voucher number is required.',
            'voucher_no.string' => 'The voucher number must be a string.',
            'ledger_id.required' => 'The ledger ID field is required.',
            'ledger_id.array' => 'The ledger ID must be an array.',
            'ledger_id.*.required' => 'Each ledger ID is required.',
            'parent_ledger_id.array' => 'The ledger Group ID must be an array.',
            'parent_ledger_id.*.required' => 'Each ledger Group is required.',
            'parent_ledger_id.required' => 'Ledger Group is required.',
            'location.required' => 'location is required.',

        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('vouchers.edit', $id)
                ->withInput() // Pass the input data back to the session
                ->withErrors($validator); // Pass the validation errors back to the session
        }
        $voucher = Voucher::find($id);
        if ($request->actionType == "amendment") {
            $revisionData = [
                ['model_type' => 'header', 'model_name' => 'Voucher', 'relation_column' => ''],
                ['model_type' => 'detail', 'model_name' => 'ItemDetail', 'relation_column' => 'voucher_id']
            ];
            Helper::documentAmendment($revisionData, $id);
            Helper::approveDocument($voucher->book_id, $voucher->id, $voucher->revision_number, $request->amend_remarks, $request->file('amend_attachments'), $voucher->approval_level, 'amendment', 0, get_class($voucher));
            $voucher->revision_number = $voucher->revision_number + 1;
            $voucher->save();
        }
        $voucher->remarks = $request->remarks;
        $voucher->amount = $request->amount;

        //currency_related_fields
        $voucher->currency_id = $request->currency_id;
        $voucher->currency_code = $request->currency_code;
        $voucher->org_currency_exg_rate = $request->orgExchangeRate;
        $voucher->org_currency_id = $request->org_currency_id;
        $voucher->org_currency_code = $request->org_currency_code;
        $voucher->org_currency_exg_rate = $request->org_currency_exg_rate;
        $voucher->comp_currency_id = $request->comp_currency_id;
        $voucher->comp_currency_code = $request->comp_currency_code;
        $voucher->comp_currency_exg_rate = $request->comp_currency_exg_rate;
        $voucher->group_currency_id = $request->group_currency_id;
        $voucher->group_currency_code = $request->group_currency_code;
        $voucher->group_currency_exg_rate = $request->group_currency_exg_rate;
        $voucher->document_date = $request->date;
        $voucher->location = $request->location;


         if ($request->status == ConstantHelper::SUBMITTED) {
                        $bookId = $voucher->book_id;
                        $docId = $voucher->id;
                        $remarks = $voucher->remarks;
                        $attachments = $request->file('attachment');
                        $currentLevel = $voucher->approval_level;
                        $revisionNumber = $voucher->revision_number ?? 0;
                        $actionType = 'submit'; // Approve // reject // submit
                        $modelName = get_class($voucher);
                        $totalValue = $voucher->amount ?? 0;
                        $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
                        $voucher->approvalStatus = $approveDocument['approvalStatus'] ?? $voucher->document_status;

        }
        else
                $voucher->approvalStatus = $request->status;


        if ($request->hasFile('document')) {
            $files = $request->file('document'); // 'document' should be an array of files
            $fileNames = [];

            foreach ($files as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('voucherDocuments');
                $file->move($destinationPath, $fileName);

                // Store the file name in an array (you can save it in the database if needed)
                $fileNames[] = $fileName;
            }

            // If you want to save multiple filenames in the database
            $voucher->document = json_encode($fileNames); // Save file names as a JSON string
        }
        $voucher->save();

        ItemDetail::where('voucher_id', $id)->delete();

        // Process item details
        $debitAmts = $request->input('debit_amt');
        $creditAmts = $request->input('credit_amt');

        $debitAmtsComp = $request->input('comp_debit_amt');
        $creditAmtsComp = $request->input('comp_credit_amt');

        $debitAmtsOrg = $request->input('org_debit_amt');
        $creditAmtsOrg = $request->input('org_credit_amt');

        $itemRemarks = $request->input('item_remarks');


        $debitAmtsGroup = $request->input('group_debit_amt');
        $creditAmtsGroup = $request->input('group_credit_amt');
        $costCenters = $request->input('cost_center_id');

        $parentLedger = $request->input('parent_ledger_id');

        foreach ($debitAmts as $index => $debitAmount) {
            if (isset($request->ledger_id[$index]) && isset($parentLedger[$index])) {
                $notename = "notes" . $index + 1;
                $ledger_id = $request->ledger_id[$index];
                $cost_center_id = $costCenters[$index]??null;
                $debitComp = $debitAmtsComp[$index] ?? 0;
                $creditComp = $creditAmtsComp[$index] ?? 0;

                $debitGroup = $debitAmtsGroup[$index] ?? 0;
                $creditGroup = $creditAmtsGroup[$index] ?? 0;

                $debitOrg = $debitAmtsOrg[$index] ?? 0;
                $creditOrg = $creditAmtsOrg[$index] ?? 0;

                $parent_ledger_id = $parentLedger[$index];

                $item_remarks = $itemRemarks[$index] ?? "";

                ItemDetail::create([
                    'voucher_id' => $id,
                    'ledger_id' => $ledger_id,
                    'debit_amt' =>$debitAmts[$index] ?? 0,
                    'credit_amt' => $creditAmts[$index] ?? 0,
                    'debit_amt_org' => $debitOrg,
                    'credit_amt_org' => $creditOrg,
                    'debit_amt_comp' => $debitComp,
                    'credit_amt_comp' => $creditComp,
                    'debit_amt_group' => $debitGroup,
                    'credit_amt_group' => $creditGroup,
                    'ledger_parent_id' => $parent_ledger_id,
                    'cost_center_id' => $cost_center_id,
                    'remarks'=>$item_remarks,
                    'notes' => $request->$notename,
                    'date' => $request->date,
                ]);
            }
            


        }

        return redirect()->route("vouchers.index")->with('success', 'Voucher updated successfully.');
    }
    public function getLedgerGroups(Request $request)
    {
        $ledgerId = $request->input('ledger_id');
        $ledger = Ledger::find($ledgerId);
        $excludeItems = $request->input('ids', []); // Expecting an array of objects [{ledger_id, ledgerGroup}]
        if ($ledger) {
            $groups = $ledger->group(); // Assuming group() is a valid method on Ledger

            // Check if the groups are a collection or a single object
            if ($groups instanceof \Illuminate\Database\Eloquent\Collection) {
                $groupItems = $groups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name
                    ];
                })->toArray();
            } elseif ($groups) {
                $groupItems = [['id' => $groups->id, 'name' => $groups->name, 'ledger_id' => $ledgerId]];
            } else {
                $groupItems = [];
            }

            // Ensure $excludeItems is an array of objects [{ledger_id, ledgerGroup}]
            $groupItems = array_filter($groupItems, function ($item) use ($excludeItems, $ledgerId) {
                foreach ($excludeItems as $exclude) {
                    if (
                        isset($exclude['ledger_id'], $exclude['ledgerGroup']) &&
                        $exclude['ledger_id'] == $ledgerId && $exclude['ledgerGroup'] == $item['id'] . ""
                    ) {
                        return false; // Exclude this group
                    }
                }
                return true;
            });

            $groupItems = array_values($groupItems); // Reindex the array
            if (empty($groupItems)) {
                return response()->json(['error' => 'All Groups Already Selected for that ledger...Please Select any other ledger'], 404);
            }

            return response()->json($groupItems);
        }

        return response()->json(['error' => 'Ledger not found'], 404);
    }
    public function revokeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            $po = Voucher::find($request->id);
            if (isset($po)) {
                $revoke = Helper::approveDocument($po->book_id, $po->id, $po->revision_number, '', null, 1, ConstantHelper::REVOKE, $po->amount, get_class($po));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $po->approvalStatus = $revoke['approvalStatus'];
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
            $po = Voucher::find($request->id);
            if (isset($po)) {
                $revoke = Helper::approveDocument($po->book_id, $po->id, $po->revision_number, '', null, 1, ConstantHelper::CANCEL, $po->amount, get_class($po));
                if ($revoke['message']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => $revoke['message'],
                    ]);
                } else {
                    $po->approvalStatus = ConstantHelper::CANCEL;
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
            } else return false;
        } else return false;
    }
    public function get_groups($allowedNames,$name=null)
        {
            $organizationId = Helper::getAuthenticatedUser()->organization_id;
            $groups = collect(); // Initialize empty collection

            foreach ($allowedNames as $name) {
                // Get all matching groups (org-specific and global)
                $matchedGroups = $group = Helper::getGroupsQuery()->where('name', $name)->get();
                $groups = $groups->merge($matchedGroups);
            }

            $allChildIds = [];

            foreach ($groups as $group) {
                $childIds = $group->getAllChildIds(); // Assume this returns array
                $childIds[] = $group->id; // Add parent group ID
                $allChildIds = array_merge($allChildIds, $childIds);
            }
            if($name=="names")
               return Group::whereIn('id',$allChildIds)->pluck('name')->toArray();

            // Remove duplicate IDs
            return array_unique($allChildIds);
        }






}