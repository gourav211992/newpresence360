<?php
namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\SaleModuleHelper;
use App\Helpers\MrnModuleHelper;
use App\Jobs\SendEmailJob;
use App\Models\ErpFinancialYear;
use App\Models\ErpMaterialIssueHeader;
use App\Models\ErpMaterialReturnHeader;
use App\Models\ErpPlHeader;
use App\Models\ErpProductionSlip;
use App\Models\ErpRateContract;
use App\Models\ErpRfqHeader;
use App\Models\ErpSaleInvoice;
use App\Models\ErpSaleInvoiceHistory;
use App\Models\ErpTransporterRequest;
use App\Models\ErpTransporterRequestBid;
use App\Models\PackingList;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\Customer;
use App\Models\ErpRgr;
use App\Services\MaterialIssue\MaterialIssue;
use DB;
use App\Helpers\Helper;
use App\Helpers\ItemHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InspectionHelper;
use App\Lib\Services\WHM\PickingJob;
use App\Lib\Services\WHM\PutawayJob;
use App\Lib\Services\WHM\RGRJob;
use App\Lib\Services\WHM\UnloadingJob;
use App\Lib\Services\WHM\WhmJob;
use App\Models\Bom;
use App\Models\Configuration;
use App\Models\ErpSaleOrder;
use App\Models\ErpLorryReceipt;
use App\Models\MrnDetail;
use App\Models\GateEntryDetail;
use App\Models\ExpenseHeader;
use App\Models\ErpSaleReturn;
use App\Models\ErpInvoiceItem;
use App\Models\ErpSoItem;
use App\Models\ErpTransportInvoice;
use App\Models\GateEntryHeader;
use App\Models\MrnHeader;
use App\Models\MrnHeaderHistory;
use App\Models\PbHeader;
use App\Models\PRHeader;
use App\Models\PRHeaderHistory;
use App\Models\PurchaseIndent;
use App\Models\PurchaseOrder;
use App\Models\InspectionHeader;
use App\Models\InspectionDetail;
use App\Models\JobOrder\JobOrder;
use App\Models\MrnBatchDetail;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Exception;
use Illuminate\Http\Request;
use Log;
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
            if ($approveDocument['message']) {
                DB::rollBack();
                return response()->json([
                    'message' => $approveDocument['message'],
                    $approveDocument['message'],
                ], 500);
            }
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
            if ($approveDocument['message']) {
                DB::rollBack();
                return response()->json([
                    'message' => $approveDocument['message'],
                    $approveDocument['message'],
                ], 500);
            }
            $saleInvoice->approval_level = $approveDocument['nextLevel'];
            $saleInvoice->document_status = $approveDocument['approvalStatus'];
            $saleInvoice->save();

            $bookTypeServiceAlias = $saleInvoice -> document_type;
            $approvalStatus = $saleInvoice -> document_status;

            if ($actionType == 'approve' && in_array($approvalStatus, [ConstantHelper::APPROVED, ConstantHelper::POSTED]) &&
            in_array($bookTypeServiceAlias, [ConstantHelper::SI_SERVICE_ALIAS, ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS])) {
                $fy = Helper::getFinancialYear($saleInvoice -> document_date);
                $fyYear = ErpFinancialYear::find($fy['id']);
                if ((int)$revisionNumber > 0) {
                    $oldSaleInvoice = ErpSaleInvoiceHistory::where('source_id', $saleInvoice -> id)
                        -> where('revision_number', $saleInvoice -> revision_number - 1) -> first();
                    if ($oldSaleInvoice) {
                        SaleModuleHelper::buildCustomerSaleInvoiceSummary($saleInvoice, $fyYear, $oldSaleInvoice);
                    }
                } else {
                    SaleModuleHelper::buildCustomerSaleInvoiceSummary($saleInvoice, $fyYear);
                }
            }

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $saleInvoice,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType document.",
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function transportInvoice(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $transportInvoice = ErpTransportInvoice::find($request->id);
            $bookId = $transportInvoice->book_id;
            $docId = $transportInvoice->id;
            $docValue = $transportInvoice->total_amount;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $transportInvoice->approval_level;
            $revisionNumber = $transportInvoice->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($transportInvoice);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            if ($approveDocument['message']) {
                DB::rollBack();
                return response()->json([
                    'message' => $approveDocument['message'],
                    $approveDocument['message'],
                ], 500);
            }
            $transportInvoice->approval_level = $approveDocument['nextLevel'];
            $transportInvoice->document_status = $approveDocument['approvalStatus'];
            $transportInvoice->save();

            DB::commit();
            return response()->json([
                'message' => "Transport Invoice $actionType successfully!",
                'data' => $transportInvoice,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType Transport Invoice document.",
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
            if ($approveDocument['message']) {
                DB::rollBack();
                return response()->json([
                    'message' => $approveDocument['message'],
                    $approveDocument['message'],
                ], 500);
            }
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
            if ($approveDocument['message']) {
                DB::rollBack();
                return response()->json([
                    'message' => $approveDocument['message'],
                    $approveDocument['message'],
                ], 500);
            }
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
            if($request->action_type == 'deviation-closed')
            {
                $remarks = $request->closing_remarks;
                $attachments = [];
            }
            else
            {
                $remarks = $request->remarks;
                $attachments = $request->file('attachment');
            }
            $currentLevel = $mrn->approval_level;
            $revisionNumber = $mrn->revision_number ?? 0;
            $actionType = $request->action_type; // Approve or reject
            $modelName = get_class($mrn);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $mrn->approval_level = $approveDocument['nextLevel'];
            if($request->action_type != 'deviation-closed')
            {
                $mrn->document_status = $approveDocument['approvalStatus'];
            }
            $mrn->save();

            // Get login user detail
            $user = Helper::getAuthenticatedUser();

            if ($request->action_type === 'deviation-closed') {
                $mrnItemIds = $mrn->items->pluck('id')->toArray();

                if (!empty($mrnItemIds)) {
                    $mrnItems = MrnDetail::whereIn('id', $mrnItemIds)->get();
                    $jobData = ErpWhmJob::find($request->closing_job_id);
                    if ($jobData) {
                        foreach ($mrnItems as $item) {
                            $pendingQty = 0;
                            $pendingUomQty = 0;
                            $batches = $item->batches()->get();
                            foreach ($batches as $batch) {
                                $pendingData = $batch->uniqueCodes()
                                    ->where('status', 'pending')
                                    ->where('job_id', $jobData->id);
                                $pendingBatchQty = $pendingData->sum('qty');
                                $pendingData->delete();
                                $hasPending = $batch->uniqueCodes()
                                    ->where('status', 'pending')
                                    ->where('job_id', $jobData->id)
                                    ->exists();
                                if (!$hasPending) {
                                    $batchQty =  ItemHelper::convertToAltUom($item->item_id, $item->uom_id, $pendingBatchQty ?? 0);
                                    $batch->decrement('inventory_uom_qty', $pendingBatchQty);
                                    $batch->decrement('quantity', $batchQty);
                                }
                                $pendingQty += $batchQty;
                                $pendingUomQty += $pendingBatchQty;
                            }
                            // Adjust accepted qty only once per item
                            $item->decrement('inventory_uom_qty', $pendingUomQty);
                            $item->decrement('order_qty', $pendingQty);
                            if ($item->gate_entry_detail_id) {
                                $item->geItem->decrement('mrn_qty', $pendingQty);
                            }
                            if ($mrn->reference_type == 'po') {
                                $item->poItem->decrement('ge_qty', $pendingQty);
                                $item->poItem->decrement('grn_qty', $pendingQty);
                            }
                            if ($mrn->reference_type == 'jo') {
                                $item->joItem->decrement('ge_qty', $pendingQty);
                                $item->joItem->decrement('grn_qty', $pendingQty);
                            }
                            if ($mrn->reference_type == 'so') {
                                $item->soItem->decrement('ge_qty', $pendingQty);
                                $item->soItem->decrement('grn_qty', $pendingQty);
                            }
                        }

                        // Final check for pending status across all items
                        $jobHasPending = ErpItemUniqueCode::where('job_id', $jobData->id)
                            ->where('status', 'pending')
                            ->exists();

                        $jobData->status = $jobHasPending ? 'deviation' : 'closed';
                        $jobData->decrement('deviation_qty', $pendingQty);
                        $jobData->save();
                    }
                }
            } else {
                // Get configuration detail
                $config = Configuration::where('type', 'organization')
                    ->where('type_id', $user->organization_id)
                    ->where('config_key', CommonHelper::ENFORCE_UIC_SCANNING)
                    ->first();

                if (in_array($mrn->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED) && $config && strtolower($config->config_value) === 'yes') {
                    (new PutawayJob)->createJob($mrn->id, 'App\Models\MrnHeader');
                }
            }

            // Mrn Purchase Summary
            if ($actionType == 'approve' && in_array($mrn->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)) {
                $fy = Helper::getFinancialYear($mrn -> document_date);
                $fyYear = ErpFinancialYear::find($fy['id']);
                if ((int)$revisionNumber > 0) {
                    $oldMrn = MrnHeaderHistory::where('mrn_header_id', $mrn -> id)
                        -> where('revision_number', $mrn -> revision_number - 1) -> first();
                    if ($oldMrn) {
                        MrnModuleHelper::buildVendorPurchaseSummary($mrn, $fyYear, $oldMrn);
                    }
                } else {
                    MrnModuleHelper::buildVendorPurchaseSummary($mrn, $fyYear);
                }
            }

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $mrn,
            ]);
        } catch (Exception $e) {
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
            // Get login user detail
            $user = Helper::getAuthenticatedUser();

            // Get configuration detail
            $config = Configuration::where('type','organization')
                ->where('type_id', $user->organization_id)
                ->whereIn('config_key', [CommonHelper::UNLOADING_REQUIRED,CommonHelper::ENFORCE_UIC_SCANNING])
                ->pluck('config_value', 'config_key');


            $gateEntry = GateEntryHeader::find($request->id);
            $bookId = $gateEntry->series_id;
            $docId = $gateEntry->id;
            $docValue = $gateEntry->total_amount;
            if($request->action_type == 'deviation-closed')
            {
                $remarks = $request->closing_remarks;
                $attachments = [];
            }
            else
            {
                $remarks = $request->remarks;
                $attachments = $request->file('attachment');
            }
            $currentLevel = $gateEntry->approval_level;
            $actionType = $request->action_type;
            $revisionNumber = $gateEntry->revision_number ?? 0;
            $modelName = get_class($gateEntry);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $gateEntry->approval_level = $approveDocument['nextLevel'];
            if($request->action_type != 'deviation-closed')
            {
                $gateEntry->document_status = $approveDocument['approvalStatus'];
            }
            $gateEntry->save();

            // Create Job
            if(in_array($gateEntry->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)
                && (isset($config[CommonHelper::UNLOADING_REQUIRED]) && $config[CommonHelper::UNLOADING_REQUIRED] == 'yes')
                && (isset($config[CommonHelper::ENFORCE_UIC_SCANNING]) && $config[CommonHelper::ENFORCE_UIC_SCANNING] == 'yes')
            ){
                (new UnloadingJob)->createJob($gateEntry->id,'App\Models\GateEntryHeader');
            }

            if ($request->action_type === 'deviation-closed') {
                $gateEntryItemIds = $gateEntry->items->pluck('id')->toArray();

                if (!empty($gateEntryItemIds)) {
                    $gateEntryItems = GateEntryDetail::whereIn('id', $gateEntryItemIds)->get();
                    $jobData = ErpWhmJob::find($request->closing_job_id);

                    if ($jobData) {
                        foreach ($gateEntryItems as $item) {
                            $pendingCodes = $item->uniqueCodes()
                                ->where('status', 'pending')
                                ->where('job_id', $jobData->id)
                                ->get();

                            $pendingQty = $pendingCodes->sum('qty');

                            // If no pending codes for this item, skip to next
                            if ($pendingCodes->isEmpty()) {
                                continue;
                            }

                            // Delete all pending codes for this job
                            $item->uniqueCodes()
                                ->where('status', 'pending')
                                ->where('job_id', $jobData->id)
                                ->delete();

                            // Check if any pending still exists for this job in this item
                            $hasPending = $item->uniqueCodes()
                                ->where('status', 'pending')
                                ->where('job_id', $jobData->id)
                                ->exists();

                            if (!$hasPending) {
                                // Adjust accepted qty only once per item
                                $item->decrement('accepted_qty', $pendingQty);
                                if($gateEntry->reference_type == 'po'){
                                    $item->po_item->decrement('ge_qty', $pendingQty);
                                }
                                if($gateEntry->reference_type == 'jo'){
                                    $item->jo_item->decrement('ge_qty', $pendingQty);
                                }
                                if($gateEntry->reference_type == 'so'){
                                    $item->soItem->decrement('ge_qty', $pendingQty);
                                }
                            }
                        }

                        // Final check for pending status across all items
                        $jobHasPending = ErpItemUniqueCode::where('job_id', $jobData->id)
                            ->where('status', 'pending')
                            ->exists();

                        $jobData->status = $jobHasPending ? 'deviation' : 'closed';
                        $jobData->save();
                    }
                }
            }
            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $gateEntry,
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
            $authUser = Helper::getAuthenticatedUser();
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
            if ($approveDocument['message']) {
                DB::rollBack();
                return response()->json([
                    'message' => $approveDocument['message'],
                    $approveDocument['message'],
                ], 500);
            }
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();
            // Get configuration detail
            $config = Configuration::where('type','organization')
                ->where('type_id', $authUser->organization_id)
                ->where('config_key', CommonHelper::ENFORCE_UIC_SCANNING)
                ->first();
            // Create job
            $miService = new MaterialIssue();
            $miService -> createWhmJob($doc, $authUser);
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
    public function pickList(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $authUser = Helper::getAuthenticatedUser();
            $doc = ErpPlHeader::find($request->id);
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
            if ($approveDocument['message']) {
                DB::rollBack();
                return response()->json([
                    'message' => $approveDocument['message'],
                    $approveDocument['message'],
                ], 500);
            }
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();
            $config = Configuration::where('type','organization')
            ->where('type_id', $authUser->organization_id)
            ->where('config_key', CommonHelper::ENFORCE_UIC_SCANNING)
            ->first();

            if(in_array($doc->document_status, [ConstantHelper::APPROVED]) && $config && strtolower($config->config_value) === 'yes'){
                (new PickingJob)->createJob($doc->id,'App\Models\ErpPlHeader');
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
    public function materialReturn(Request $request)
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
            if ($approveDocument['message']) {
                DB::rollBack();
                return response()->json([
                    'message' => $approveDocument['message'],
                    $approveDocument['message'],
                ], 500);
            }
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
            if ($approveDocument['message']) {
                DB::rollBack();
                return response()->json([
                    'message' => $approveDocument['message'],
                    $approveDocument['message'],
                ], 500);
            }
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
    public function rfq(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $doc = ErpRfqHeader::find($request->id);
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

            //Purchase Return Summary
            if ($actionType == 'approve' && in_array($mrn->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED)) {
                $fy = Helper::getFinancialYear($mrn -> document_date);
                $fyYear = ErpFinancialYear::find($fy['id']);
                if ((int)$revisionNumber > 0) {
                    $oldMrn = PRHeaderHistory::where('header_id', $mrn -> id)
                        -> where('revision_number', $mrn -> revision_number - 1) -> first();
                    if ($oldMrn) {
                        MrnModuleHelper::buildVendorPurchaseReturnSummary($mrn, $fyYear, $oldMrn);
                    }
                } else {
                    MrnModuleHelper::buildVendorPurchaseReturnSummary($mrn, $fyYear);
                }
            }

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

            // Get login user detail
            $user = Helper::getAuthenticatedUser();

            // Get configuration detail
            $config = Configuration::where('type','organization')
                ->where('type_id', $user->organization_id)
                ->where('config_key', CommonHelper::ENFORCE_UIC_SCANNING)
                ->first();

            if(in_array($inspection->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED) && $config && strtolower($config->config_value) === 'yes'){
                (new PutawayJob)->createJob($inspection->id,'App\Models\MrnHeader');
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

    public function item(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $item = Item::find($request->id);
            $bookId = $item->book_id;
            $docId = $item->id;
            $docValue = 0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $item->approval_level;
            $revisionNumber = $item->revision_number ?? 0;
            $actionType = $request->action_type;
            $modelName = get_class($item);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $item->approval_level = $approveDocument['nextLevel'];
            $document_status = $approveDocument['approvalStatus'];
            $status = $request->status;
            $item->document_status = $document_status;
            if (in_array($document_status, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED]) ) {
                if ($revisionNumber == 0) {
                    $item->status = ConstantHelper::ACTIVE;
                }
            } else {
                $item->status = $document_status;
            }

            $item->save();

            DB::commit();
            return response()->json([
                'message' => "Document $actionType successfully!",
                'data' => $item,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType item document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function vendor(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $vendor = Vendor::find($request->id);
            $bookId = $vendor->book_id;
            $docId = $vendor->id;
            $docValue = 0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $vendor->approval_level ?? 1;
            $revisionNumber = $vendor->revision_number ?? 0;
            $actionType = $request->action_type;
            $modelName = get_class($vendor);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            $vendor->approval_level = $approveDocument['nextLevel'] ?? 1;
            $approvalStatus = $approveDocument['approvalStatus'];
            $status = $request->status;
            $vendor->document_status = $approvalStatus;
            if (in_array($approvalStatus, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                if ($revisionNumber == 0) {
                    $vendor->status = ConstantHelper::ACTIVE;
                }
                 // ** START: Call createPartyLedger if conditions are met **
                    $createVendorLedger = $request->input('create_ledger') && $request->input('create_ledger') == 1;
                    $hiddenLedgerVendorName = $request->input('hidden_ledger_vendor_name');
                    $hiddenLedgerVendorCode = $request->input('hidden_ledger_vendor_code');
                    $ledgerGroupId = $request->input('ledger_group_id');

                    if ($createVendorLedger && !empty($hiddenLedgerVendorName) && !empty($hiddenLedgerVendorCode) && !empty($ledgerGroupId)) {
                        try {
                            $result = Helper::createPartyLedger(
                                'vendor',
                                $hiddenLedgerVendorName,
                                $hiddenLedgerVendorCode,
                                $ledgerGroupId
                            );

                            if (!$result['success']) {
                                Log::error('Error creating party ledger: ' . $result['message']);
                                DB::rollBack();
                                return response()->json([
                                    'status' => false,
                                    'message' => $result['message'],
                                    'data' => $vendor,
                                ], 500);
                            }
                            $ledgerId = $result['data']['ledger_id'] ?? null;
                            $ledgerGroupId = $result['data']['ledger_group_id'] ?? null;
                            $vendor->ledger_id = $ledgerId;
                            $vendor->ledger_group_id = $ledgerGroupId;
                            $vendor->ledger_group_id = $ledgerGroupId;
                            $vendor->create_ledger = 0;
                        } catch (Exception $e) {
                            Log::error('Exception creating party ledger: ' . $e->getMessage(), [
                                'trace' => $e->getTraceAsString()
                            ]);
                            DB::rollBack();
                            return response()->json([
                                'status' => false,
                                'message' =>  $e->getMessage(),
                                'data' => $vendor,
                            ], 500);
                        }
                    }
                // ** END: Call createPartyLedger if conditions are met **
            } else {
                $vendor->status = $approvalStatus;
            }
            $vendor->save();

            DB::commit();
            return response()->json([
                'message' => "Vendor document $actionType successfully!",
                'data' => $vendor,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType vendor document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function customer(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $customer = Customer::find($request->id);
            $bookId = $customer->book_id;
            $docId = $customer->id;
            $docValue = 0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $customer->approval_level ?? 1;
            $revisionNumber = $customer->revision_number ?? 0;
            $actionType = $request->action_type;
            $modelName = get_class($customer);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);

            $customer->approval_level = $approveDocument['nextLevel'] ?? 1;
            $approvalStatus = $approveDocument['approvalStatus'];
            $status = $request->status;
            $customer->document_status = $approvalStatus;

            if (in_array($approvalStatus, [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])) {
                 if ($revisionNumber == 0) {
                    $customer->status = ConstantHelper::ACTIVE;
                 }
                    // ** START: Call createPartyLedger if conditions are met **
                    $createCustomerLedger = $request->input('create_ledger') && $request->input('create_ledger') == 1;
                    $hiddenLedgerCustomerName = $request->input('hidden_ledger_customer_name');
                    $hiddenLedgerCustomerCode = $request->input('hidden_ledger_customer_code');
                    $ledgerGroupId = $request->input('ledger_group_id');

                    if ($createCustomerLedger && !empty($hiddenLedgerCustomerName) && !empty($hiddenLedgerCustomerCode) && !empty($ledgerGroupId)) {
                        try {
                            $result = Helper::createPartyLedger(
                                'customer',
                                $hiddenLedgerCustomerName,
                                $hiddenLedgerCustomerCode,
                                $ledgerGroupId
                            );

                            if (!$result['success']) {
                                Log::error('Error creating party ledger: ' . $result['message']);
                                DB::rollBack();
                                return response()->json([
                                    'status' => false,
                                    'message' => $result['message'],
                                    'data' => $customer,
                                ], 500);
                            }
                            $ledgerId = $result['data']['ledger_id'] ?? null;
                            $ledgerGroupId = $result['data']['ledger_group_id'] ?? null;
                            $customer->ledger_id = $ledgerId;
                            $customer->ledger_group_id = $ledgerGroupId;
                            $customer->ledger_group_id = $ledgerGroupId;
                            $customer->create_ledger = 0;
                        } catch (Exception $e) {
                            Log::error('Exception creating party ledger: ' . $e->getMessage(), [
                                'trace' => $e->getTraceAsString()
                            ]);
                            DB::rollBack();
                            return response()->json([
                                'status' => false,
                                'message' =>  $e->getMessage(),
                                'data' => $customer,
                            ], 500);
                        }
                    }
                // ** END: Call createPartyLedger if conditions are met **
            } else {
                $customer->status = $approvalStatus;
            }

            $customer->save();

            DB::commit();
            return response()->json([
                'message' => "Customer document $actionType successfully!",
                'data' => $customer,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType customer document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }



     public function lorryReceipt(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            $lr = ErpLorryReceipt::find($request->id);
            $bookId = $lr->book_id;
            $docId = $lr->id;
            $docValue =$lr->total_charges ??  0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachment');
            $currentLevel = $lr->approval_level;
            $revisionNumber = $lr->revision_number ?? 0;
            $actionType = $request->action_type;

            $modelName = get_class($lr);
            $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue, $modelName);
            if ($approveDocument['message'])
            {

                DB::rollBack();

                return response()->json([

                    'message' => $approveDocument['message'],

                    $approveDocument['message'],

                ], 500);

            }
            $lr->approval_level = $approveDocument['nextLevel'];

            $lr->document_status = $approveDocument['approvalStatus'];

            $lr->save();

            DB::commit();
            return response()->json([
                'message' => "Lorry Receipt document $actionType successfully!",
                'data' => $lr,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType lorry receipt document.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   public function rgr(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:255',
            'attachment' => 'nullable'
        ]);

        DB::beginTransaction();
        try {
            $doc = ErpRgr::find($request->id);

            if (!$doc) {
                return response()->json([
                    'message' => 'RGR not found',
                    'error' => '',
                ], 404);
            }

            $bookId = $doc->book_id;
            $docId = $doc->id;
            $docValue = 0;
            $remarks = $request->remarks;
            $attachments = $request->file('attachments');
            $currentLevel = $doc->approval_level;
            $revisionNumber = $doc->revision_number ?? 0;
            $actionType = $request->action_type;
            $modelName = get_class($doc);
            $approveDocument = Helper::approveDocument( $bookId,$docId,$revisionNumber,$remarks,$attachments,$currentLevel,$actionType,$docValue,$modelName);
            $doc->approval_level = $approveDocument['nextLevel'];
            $doc->document_status = $approveDocument['approvalStatus'];
            $doc->save();

            if (in_array($doc->document_status, [
                ConstantHelper::APPROVED,
                ConstantHelper::APPROVAL_NOT_REQUIRED
            ])) {
                (new RGRJob)->createJob($doc->id, 'App\Models\ErpRgr');
            }
            DB::commit();

            return response()->json([
                'message' => "RGR $actionType successfully!",
                'data' => $doc,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error occurred while $actionType RGR",
                'error' => $e->getMessage() . ' on line ' . $e->getLine(),
            ], 500);
        }
    }
}
