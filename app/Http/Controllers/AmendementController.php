<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use App\Models\Bom;
use App\Models\ExpenseDetail;
use App\Models\ExpenseDetailHistory;
use App\Models\ExpenseHeader;
use App\Models\ExpenseHeaderHistory;
use App\Models\ExpenseItemAttribute;
use App\Models\ExpenseItemAttributeHistory;
use App\Models\ExpenseTed;
use App\Models\ExpenseTedHistory;
use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnAttribute;
use App\Models\MrnItemLocation;
use App\Models\MrnExtraAmount;
use App\Models\MrnHeaderHistory;
use App\Models\MrnDetailHistory;
use App\Models\MrnAttributeHistory;
use App\Models\MrnItemLocationHistory;
use App\Models\MrnExtraAmountHistory;
use DB;
use Illuminate\Http\Request;
class AmendementController extends Controller
{
    # MRN Ammendement
    public function mrnAmendmentSubmit(Request $request)
    {
        DB::beginTransaction();
        try {
            // Header History
            // dd($request->id);
            $mrnHeader = MrnHeader::find($request->id);
            if(!$mrnHeader) {
                return response()->json(['error' => 'Mrn Header not found'], 404);
            }
            $mrnHeaderData = $mrnHeader->toArray();
            unset($mrnHeaderData['id']); // You might want to remove the primary key, 'id'
            $mrnHeaderData['mrn_header_id'] = $mrnHeader->id;
            $headerHistory = MrnHeaderHistory::create($mrnHeaderData);
            $headerHistoryId = $headerHistory->id;

            // Detail History
            $mrnDetails = MrnDetail::where('mrn_header_id', $mrnHeader->id)->get();
            if(!empty($mrnDetails)){
                foreach($mrnDetails as $key => $detail){
                    $mrnDetailData = $detail->toArray();
                    unset($mrnDetailData['id']); // You might want to remove the primary key, 'id'
                    $mrnDetailData['mrn_detail_id'] = $detail->id;
                    $mrnDetailData['mrn_header_history_id'] = $headerHistoryId;
                    $detailHistory = MrnDetailHistory::create($mrnDetailData);
                    $detailHistoryId = $detailHistory->id;

                    // Attribute History
                    $mrnAttributes = MrnAttribute::where('mrn_header_id', $mrnHeader->id)
                        ->where('mrn_detail_id', $detail->id)
                        ->get();
                    if(!empty($mrnAttributes)){
                        foreach($mrnAttributes as $key1 => $attribute){
                            $mrnAttributeData = $attribute->toArray();
                            unset($mrnAttributeData['id']); // You might want to remove the primary key, 'id'
                            $mrnAttributeData['mrn_attribute_id'] = $attribute->id;
                            $mrnAttributeData['mrn_header_history_id'] = $headerHistoryId;
                            $mrnAttributeData['mrn_detail_history_id'] = $detailHistoryId;
                            $attributeHistory = MrnAttributeHistory::create($mrnAttributeData);
                            $attributeHistoryId = $attributeHistory->id;
                        }
                    }

                    // Item Locations History
                    $itemLocations = MrnItemLocation::where('mrn_header_id', $mrnHeader->id)
                        ->where('mrn_detail_id', $detail->id)
                        ->get();
                    if(!empty($itemLocations)){
                        foreach($itemLocations as $key2 => $location){
                            $itemLocationData = $location->toArray();
                            unset($itemLocationData['id']); // You might want to remove the primary key, 'id'
                            $itemLocationData['mrn_item_location_id'] = $location->id;
                            $itemLocationData['mrn_header_history_id'] = $headerHistoryId;
                            $itemLocationData['mrn_detail_history_id'] = $detailHistoryId;
                            $itemLocationHistory = MrnItemLocationHistory::create($itemLocationData);
                            $itemLocationHistoryId = $itemLocationHistory->id;
                        }
                    }

                    // Extra Amount Item History
                    $itemExtraAmounts = MrnExtraAmount::where('mrn_header_id', $mrnHeader->id)
                        ->where('mrn_detail_id', $detail->id)
                        ->where('ted_level', '=', 'Item')
                        ->get();

                    if(!empty($itemExtraAmounts)){
                        foreach($itemExtraAmounts as $key4 => $extraAmount){
                            $extraAmountData = $extraAmount->toArray();
                            unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                            $extraAmountData['mrn_extra_amount_id'] = $extraAmount->id;
                            $extraAmountData['mrn_header_history_id'] = $headerHistoryId;
                            $extraAmountData['mrn_detail_history_id'] = $detailHistoryId;
                            $extraAmountDataHistory = MrnExtraAmountHistory::create($extraAmountData);
                            $extraAmountDataId = $extraAmountDataHistory->id;
                        }
                    }
                }
            }

            // Extra Amount Header History
            $mrnExtraAmounts = MrnExtraAmount::where('mrn_header_id', $mrnHeader->id)
                ->where('ted_level', '=', 'Header')
                ->get();

            if(!empty($mrnExtraAmounts)){
                foreach($mrnExtraAmounts as $key4 => $extraAmount){
                    $extraAmountData = $extraAmount->toArray();
                    unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                    $extraAmountData['mrn_extra_amount_id'] = $extraAmount->id;
                    $extraAmountData['mrn_header_history_id'] = $headerHistoryId;
                    $extraAmountDataHistory = MrnExtraAmountHistory::create($extraAmountData);
                    $extraAmountDataId = $extraAmountDataHistory->id;
                }
            }

            $randNo = rand(10000,99999);

            $revisionNumber = "MRN".$randNo;
            $mrnHeader->revision_number += 1;
            $mrnHeader->status = "draft";
            $mrnHeader->document_status = "draft";
            $mrnHeader->save();

            /*Create document submit log*/
            if ($mrnHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $mrnHeader->series_id;
                $docId = $mrnHeader->id;
                $remarks = $mrnHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $mrnHeader->approval_level;
                $revisionNumber = $mrnHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
            }

            DB::commit();
            return response()->json([
                'message' => 'Amendement done successfully!',
                'data' => $mrnHeader,
            ]);
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while amendement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    # Expense Ammendement
    public function expense(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable',
            'attachment' => 'nullable'
        ]);
        DB::beginTransaction();
        try {
            // Header History
            $expenseHeader = ExpenseHeader::find($request->id);
            if(!$expenseHeader) {
                return response()->json(['error' => 'Expense Header not found'], 404);
            }
            $expenseHeaderData = $expenseHeader->toArray();
            unset($expenseHeaderData['id']); // You might want to remove the primary key, 'id'
            $expenseHeaderData['header_id'] = $expenseHeader->id;
            $headerHistory = ExpenseHeaderHistory::create($expenseHeaderData);
            $headerHistoryId = $headerHistory->id;

            // Detail History
            $expenseDetails = ExpenseDetail::where('expense_header_id', $expenseHeader->id)->get();
            if(!empty($expenseDetails)){
                foreach($expenseDetails as $key => $detail){
                    $expenseDetailData = $detail->toArray();
                    unset($expenseDetailData['id']); // You might want to remove the primary key, 'id'
                    $expenseDetailData['header_id'] = $detail->expense_header_id;
                    $expenseDetailData['detail_id'] = $detail->id;
                    $expenseDetailData['header_history_id'] = $headerHistoryId;
                    $detailHistory = ExpenseDetailHistory::create($expenseDetailData);
                    $detailHistoryId = $detailHistory->id;

                    // Attribute History
                    $expenseAttributes = ExpenseItemAttribute::where('expense_header_id', $expenseHeader->id)
                        ->where('expense_detail_id', $detail->id)
                        ->get();
                    if(!empty($expenseAttributes)){
                        foreach($expenseAttributes as $key1 => $attribute){
                            $expenseAttributeData = $attribute->toArray();
                            unset($expenseAttributeData['id']); // You might want to remove the primary key, 'id'
                            $expenseAttributeData['header_id'] = $detail->expense_header_id;
                            $expenseAttributeData['detail_id'] = $detail->id;
                            $expenseAttributeData['attribute_id'] = $attribute->id;
                            $expenseAttributeData['header_history_id'] = $headerHistoryId;
                            $expenseAttributeData['detail_history_id'] = $detailHistoryId;
                            $attributeHistory = ExpenseItemAttributeHistory::create($expenseAttributeData);
                            $attributeHistoryId = $attributeHistory->id;
                        }
                    }

                    // Expense Item TED History
                    $itemExtraAmounts = ExpenseTed::where('expense_header_id', $expenseHeader->id)
                        ->where('expense_detail_id', $detail->id)
                        ->where('ted_level', '=', 'Item')
                        ->get();

                    if(!empty($itemExtraAmounts)){
                        foreach($itemExtraAmounts as $key4 => $extraAmount){
                            $extraAmountData = $extraAmount->toArray();
                            unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                            $expenseAttributeData['header_id'] = $detail->expense_header_id;
                            $expenseAttributeData['detail_id'] = $detail->id;
                            $expenseAttributeData['header_history_id'] = $headerHistoryId;
                            $expenseAttributeData['detail_history_id'] = $detailHistoryId;
                            $expenseAttributeData['expense_ted_id'] = $extraAmount->id;
                            $extraAmountDataHistory = ExpenseTedHistory::create($extraAmountData);
                            $extraAmountDataId = $extraAmountDataHistory->id;
                        }
                    }
                }
            }

            // Expense Header TED History
            $expenseExtraAmounts = ExpenseTed::where('expense_header_id', $expenseHeader->id)
                ->where('ted_level', '=', 'Header')
                ->get();

            if(!empty($expenseExtraAmounts)){
                foreach($expenseExtraAmounts as $key4 => $extraAmount){
                    $extraAmountData = $extraAmount->toArray();
                    unset($extraAmountData['id']); // You might want to remove the primary key, 'id'
                    $expenseAttributeData['header_id'] = $detail->expense_header_id;
                    $expenseAttributeData['header_history_id'] = $headerHistoryId;
                    $expenseAttributeData['expense_ted_id'] = $extraAmount->id;
                    $extraAmountDataHistory = ExpenseTedHistory::create($extraAmountData);
                    $extraAmountDataId = $extraAmountDataHistory->id;
                }
            }

            $randNo = rand(10000,99999);

            $revisionNumber = "Expense".$randNo;
            $expenseHeader->revision_number += 1;
            $expenseHeader->status = "draft";
            $expenseHeader->document_status = "draft";
            $expenseHeader->save();

            /*Create document submit log*/
            if ($expenseHeader->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $expenseHeader->series_id;
                $docId = $expenseHeader->id;
                $remarks = $expenseHeader->remarks;
                $attachments = $request->file('attachment');
                $currentLevel = $expenseHeader->approval_level;
                $revisionNumber = $expenseHeader->revision_number ?? 0;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
            }

            DB::commit();
            return response()->json([
                'message' => 'Amendement done successfully!',
                'data' => $expenseHeader,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while amendement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
