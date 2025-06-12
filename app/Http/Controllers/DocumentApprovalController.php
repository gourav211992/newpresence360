<?php
namespace App\Http\Controllers;

use App\Helpers\ConstantHelper;
use App\Jobs\SendEmailJob;
use App\Models\ErpMaterialIssueHeader;
use App\Models\ErpMaterialReturnHeader;
use App\Models\ErpProductionSlip;
use App\Models\ErpRateContract;
use App\Models\ErpSaleInvoice;
use App\Models\ErpTransporterRequest;
use App\Models\ErpTransporterRequestBid;
use App\Models\PackingList;
use App\Models\Vendor;
use DB;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\InspectionHelper;

use App\Models\Bom;
use App\Models\ErpSaleOrder;
use App\Models\MrnDetail;
use App\Models\ExpenseHeader;
use App\Models\ErpSaleReturn;
use App\Models\ErpInvoiceItem;
use App\Models\ErpSoItem;
use App\Models\GateEntryHeader;
use App\Models\MrnHeader;
use App\Models\PbHeader;
use App\Models\PRHeader;
use App\Models\PurchaseIndent;
use App\Models\PurchaseOrder;
use App\Models\InspectionHeader;
use App\Models\InspectionDetail;
use App\Models\JobOrder\JobOrder;
use Exception;
use Illuminate\Http\Request;
class DocumentApprovalController extends Controller
{
    # Bom Approval
    public function bom(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $bom = Bom::find($request->id);
            $bookId = $bom->book_id;
            $docId = $bom->id;
            $docValue = $bom->total_value;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $bom->approval_level;
            $revisionNumber = $bom->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($bom);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $bom->approval_level = $approveDocument['nextLevel'];
            $bom->document_status = $approveDocument['approvalStatus'];
            $bom->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $bom,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType bom document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    # PO Approval
    public function po(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $po = PurchaseOrder::find($request->id);
            $bookId = $po->book_id;
            $docId = $po->id;
            $docValue = $po->grand_total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $po->approval_level;
            $revisionNumber = $po->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($po);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $po->approval_level = $approveDocument['nextLevel'];
            $po->document_status = $approveDocument['approvalStatus'];
            $po->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $po,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType po document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    # Jo Approval
    public function jo(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $po = JobOrder::find($request->id);
            $bookId = $po->book_id;
            $docId = $po->id;
            $docValue = $po->grand_total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $po->approval_level;
            $revisionNumber = $po->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($po);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $po->approval_level = $approveDocument['nextLevel'];
            $po->document_status = $approveDocument['approvalStatus'];
            $po->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $po,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType po document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    # PO Approval
    public function pi(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $pi = PurchaseIndent::find($request->id);
            $bookId = $pi->book_id;
            $docId = $pi->id;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $pi->approval_level;
            $revisionNumber = $pi->revision_number ?? 0;
            $actionType = $request->action_type;
            $modelName = get_class($pi);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, 0, $modelName);
            $pi->approval_level = $approveDocument['nextLevel'];
            $pi->document_status = $approveDocument['approvalStatus'];
            $pi->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $pi,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType pi document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function saleOrder(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $saleOrder = ErpSaleOrder::find($request->id);
            $bookId = $saleOrder->book_id;
            $docId = $saleOrder->id;
            $docValue = $saleOrder->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $saleOrder->approval_level;
            $revisionNumber = $saleOrder->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($saleOrder);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $saleOrder->approval_level = $approveDocument['nextLevel'];
            $saleOrder->document_status = $approveDocument['approvalStatus'];
            $saleOrder->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $saleOrder,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType Sale Order document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    //Sales Invoice / Delivery Note / Delivery Note CUM Invoice/ Lease Invoice
    public function saleInvoice(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $saleInvoice = ErpSaleInvoice::find($request->id);
            $bookId = $saleInvoice->book_id;
            $docId = $saleInvoice->id;
            $docValue = $saleInvoice->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $saleInvoice->approval_level;
            $revisionNumber = $saleInvoice->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($saleInvoice);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $saleInvoice->approval_level = $approveDocument['nextLevel'];
            $saleInvoice->document_status = $approveDocument['approvalStatus'];
            $saleInvoice->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $saleInvoice,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType Sale Order document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function packingList(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $packingList = PackingList::find($request->id);
            $bookId = $packingList->book_id;
            $docId = $packingList->id;
            $docValue = 0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $packingList->approval_level;
            $revisionNumber = $packingList->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($packingList);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $packingList->approval_level = $approveDocument['nextLevel'];
            $packingList->document_status = $approveDocument['approvalStatus'];
            $packingList->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $packingList,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType Sale Order document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    //Sale Return Apporval
    public function saleReturn(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $saleReturn = ErpSaleReturn::find($request->id);
            $return_items = $saleReturn->items;
            $bookId = $saleReturn->book_id;
            $docId = $saleReturn->id;
            $docValue = $saleReturn->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $saleReturn->approval_level;
            $revisionNumber = $saleReturn->revision_number ?? 0;
            $actionType = $request->action_type;
            $modelName = get_class($saleReturn);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $saleReturn->approval_level = $approveDocument['nextLevel'];
            $saleReturn->document_status = $approveDocument['approvalStatus'];
            $saleReturn->save();
            if ($actionType == 'reject') {
                foreach ($return_items as $items) {
                    if ($items->si_item_id) {
                        $siItem = ErpInvoiceItem::find($items->si_item_id);
                        if (isset($siItem)) {
                            $siItem->srn_qty -= $items->order_qty;
                            $siItem->dnote_qty += $items->order_qty;
                            if ($siItem->header->invoice_required) {
                                $siItem->invoice_qty += $items->order_qty;
                            }
                            $siItem->save();

                            if ($siItem->so_item_id) {
                                $soItem = ErpSoItem::find($siItem->so_item_id);
                                if (isset($soItem)) {
                                    $soItem->srn_qty -= $items->order_qty;
                                    $soItem->dnote_qty += $items->order_qty;
                                    $soItem->order_qty += $items->order_qty;
                                    if ($siItem->header->invoice_required) {
                                        $soItem->invoice_qty += $items->order_qty;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {

            }
            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $saleReturn,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType Sale Return document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    //transporter
    public function transporter(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $tr = ErpTransporterRequest::find($request->id);
            $bookId = $tr->book_id;
            $docId = $tr->id;
            $docValue = $tr->total_weight;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $tr->approval_level;
            $actionType = $request->action_type;
            $modelName = get_class($tr);
            $approveDocument = Helper::approveDocument($bookId, $docId, 0, $remarks, [], $currentLevel, $actionType, $docValue, $modelName);
            $tr->approval_level = $approveDocument['nextLevel'];
            $tr->document_status = $approveDocument['approvalStatus'];
            if ($actionType == 'shortlist') {
                $tr->selected_bid_id = $request->bid_id;

                // Update all bids' status for the given transporter_request_id
                ErpTransporterRequestBid::where('transporter_request_id', $tr->id)->whereNotIn('bid_status',["cancelled"])
                    ->update(['bid_status' => ConstantHelper::SUBMITTED]);

                // Fetch the specific bid that needs to be shortlisted
                $bid_details = ErpTransporterRequestBid::find($request->bid_id);

                if ($bid_details) { // Ensure bid exists before modifying
                    $bid_details->bid_status = 'shortlisted';
                    $bid_details->save(); // Save the shortlisted bid
                }
                $transporter_ids = json_decode($tr->transporter_ids);
                if ($transporter_ids) {
                    $vendors = Vendor::whereIn('id', $transporter_ids)->get(); // Keep as a collection
                }
                else{
                    $vendors = Vendor::withDefaultGroupCompanyOrg()->get();
                }
                foreach ($vendors as $vendor) {
                    $sendTo = $vendor->email;
                    $title = "New Transporter Request";
                    $bidLink = route('supplier.transporter.index',[$vendor->id]); // Generate route in PHP
                    $name = $vendor->company_name;
                    $bid_name = $tr->document_number;
                    $description = <<<HTML
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px #ccc;">
                        <tr>
                            <td align="left" style="padding: 10px 0;">
                                <h2 style="color: #333;">Bid Shortlisting Notification – Vehicle Details Required</h2>
                                <p>Dear {$name},</p>
                                <p>We are pleased to inform you that you have been shortlisted for the bid <strong>{$bid_name}</strong>.</p>
                                <p>As the next step, we kindly request you to provide us with the necessary vehicle details to proceed further.</p>
                                <p>Timely submission of this information is essential to finalize the process.</p>
                                <p style="text-align: center;">
                                    <a href="{$bidLink}" target="_blank" style="background-color: #007bff; color: #ffffff; padding: 12px 20px; border-radius: 5px; font-size: 16px; text-decoration: none; display: inline-block; font-weight: bold;">
                                        Submit Vehicle Details
                                    </a>
                                </p>
                                <p>If you have any questions or require further clarification, please feel free to contact us.</p>
                                <p>We appreciate your cooperation and look forward to working together.</p>
                            </td>
                        </tr>
                    </table>
                    HTML;
                    if (!$vendors || !isset($vendors->email)) {
                        continue;
                    }

                    dispatch(new SendEmailJob($vendors, $title, $description));
                }

            }
            if ($actionType == 'confirmed') {

                // Update all bids' status for the given transporter_request_id
                ErpTransporterRequestBid::where('transporter_request_id', $tr->id)
                    ->update(['bid_status' => ConstantHelper::SUBMITTED]);

                // Fetch the specific bid that needs to be shortlisted
                $bid_details = ErpTransporterRequestBid::find($request->bid_id);

                if ($bid_details) { // Ensure bid exists before modifying
                    $bid_details->bid_status = 'shortlisted';
                    $bid_details->save(); // Save the shortlisted bid
                }
            }

            if ($actionType == 'cancelled') {

                // Update all bids' status for the given transporter_request_id
                ErpTransporterRequestBid::where('transporter_request_id', $tr->id)
                    ->update(['bid_status' => ConstantHelper::SUBMITTED]);

                // Fetch the specific bid that needs to be shortlisted
                $bid_details = ErpTransporterRequestBid::find($request->bid_id);

                if ($bid_details) { // Ensure bid exists before modifying
                    $bid_details->bid_status = 'shortlisted';
                    $bid_details->save(); // Save the shortlisted bid
                }
            }

            $tr->save();
            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $tr,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType Sale Return document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // MRN Document Approval
    public function mrn(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $mrn = MrnHeader::find($request->id);
            $bookId = $mrn->series_id;
            $docId = $mrn->id;
            $docValue = $mrn->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $mrn->approval_level;
            $revisionNumber = $mrn->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($mrn);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $mrn->approval_level = $approveDocument['nextLevel'];
            $mrn->document_status = $approveDocument['approvalStatus'];
            $mrn->save();



            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $mrn,
            ]);
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType mrn document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Gate Entry Document Approval
    public function gateEntry(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $expense = GateEntryHeader::find($request->id);
            $bookId = $expense->series_id;
            $docId = $expense->id;
            $docValue = $expense->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $expense->approval_level;
            $revisionNumber = $expense->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($expense);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $expense->approval_level = $approveDocument['nextLevel'];
            $expense->document_status = $approveDocument['approvalStatus'];
            $expense->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $expense,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType gate entry document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Expense Document Approval
    public function expense(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $expense = ExpenseHeader::find($request->id);
            $bookId = $expense->series_id;
            $docId = $expense->id;
            $docValue = $expense->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $expense->approval_level;
            $revisionNumber = $expense->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($expense);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $expense->approval_level = $approveDocument['nextLevel'];
            $expense->document_status = $approveDocument['approvalStatus'];
            $expense->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $expense,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType expense document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // MRN Document Approval
    public function purchaseBill(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $mrn = PbHeader::find($request->id);
            $bookId = $mrn->series_id;
            $docId = $mrn->id;
            $docValue = $mrn->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $mrn->approval_level;
            $revisionNumber = $mrn->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($mrn);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $mrn->approval_level = $approveDocument['nextLevel'];
            $mrn->document_status = $approveDocument['approvalStatus'];
            $mrn->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $mrn,
            ]);
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType purchase bill document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function materialIssue(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = ErpMaterialIssueHeader::find($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = $doc->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $doc->approval_level;
            $revisionNumber = $doc->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($doc);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $doc,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function materialReturn (Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = ErpMaterialReturnHeader::find($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = $doc->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $doc->approval_level;
            $revisionNumber = $doc->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($doc);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $doc,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function rateContract (Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = ErpRateContract::find($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = 0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $doc->approval_level;
            $revisionNumber = $doc->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($doc);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $doc,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType",
                'error' => $e->getMessage().$e->getLine().$e->getFile(),
            ], 500);
        }
    }
    public function productionSlip(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = ErpProductionSlip::find($request->id);
            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = $doc->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $doc->approval_level;
            $revisionNumber = $doc->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($doc);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();

            if($doc->is_last_station && in_array($doc->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)) {
                foreach($doc->items as $pslipItem) {
                    $moProduct = $pslipItem?->mo_product ?? null;
                    if($moProduct) {
                        $moProduct->pwoMapping->pslip_qty += floatval($pslipItem->qty);
                        $moProduct->pwoMapping->save();
                        if($moProduct?->soItem) {
                            $moProduct->soItem->pslip_qty += floatval($pslipItem->qty);
                            $moProduct->soItem->save();
                        }
                    }
                }
            }
            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $doc,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function purchaseReturn(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $mrn = PRHeader::find($request->id);
            $bookId = $mrn->series_id;
            $docId = $mrn->id;
            $docValue = $mrn->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $mrn->approval_level;
            $revisionNumber = $mrn->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($mrn);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $mrn->approval_level = $approveDocument['nextLevel'];
            $mrn->document_status = $approveDocument['approvalStatus'];
            $mrn->save();

            DB::commit();
            // return response()->json([
            //     'message' => "Document $actionType successfully!",
            //     'data' => $mrn,
            // ]);
            return redirect()->route('purchase-return.index');
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType mrn document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // MRN Document Approval
    public function inspection(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $inspection = InspectionHeader::find($request->id);
            $bookId = $inspection->series_id;
            $docId = $inspection->id;
            $docValue = $inspection->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $inspection->approval_level;
            $revisionNumber = $inspection->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($inspection);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $inspection->approval_level = $approveDocument['nextLevel'];
            $inspection->document_status = $approveDocument['approvalStatus'];
            $inspection->save();

            if($inspection->document_status == ConstantHelper::APPROVED) {
                $updateMrn = InspectionHelper::updateMrnDetail($inspection);
            }

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $inspection,
            ]);
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType mrn document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
