<?php

namespace App\Http\Controllers\BillOfMaterial;

use App\Exports\BomImportErrorExport;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BomImportRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BomImportData;
use App\Models\Organization;
use App\Models\Bom;
use App\Models\BomAttribute;
use App\Models\BomDetail;
use App\Models\BomNormsCalculation;
use App\Models\BomUpload;
use Illuminate\Http\Request;
use DB;

class BomImportController extends Controller
{
    // downloadSample
    public function downloadSample(Request $request)
    {
        $filePath = public_path('templates/Bom_Sample.xlsx');
        $fileName = 'Bom_Sample.xlsx';

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }

    public function import(Request $request)
    {
        $parentUrl = request()->segments()[0];
        $servicesAliasParam = request()->segments()[0] == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl, $servicesAliasParam);

        if (count($servicesBooks['services']) == 0) {
            return redirect()->back()->with('error', 'Service book not found.');
        }
        $books = Helper::getBookSeriesNew($servicesAliasParam, $parentUrl, true)->get();
        $books = $books->filter(function ($book) {
            return optional($book->patterns->first())->series_numbering === 'Auto';
        });
        if (count($books) == 0) {
            return redirect()->back()->with('error', 'Book series not found.');
        }
        $routeAlias = $servicesBooks['services'][0]?->alias ?? null;
        if ($routeAlias == ConstantHelper::BOM_SERVICE_ALIAS) {
            $routeAlias = 'bill-of-material';
        } else {
            $routeAlias = 'quotation-bom';
        }
        return view('billOfMaterial.import', [
            'books' => $books,
            'servicesBooks' => $servicesBooks,
            'serviceAlias' => $servicesAliasParam,
            'routeAlias' => $routeAlias
        ]);
    }

    #Bill of material store
    // public function importSave(BomImportRequest $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $bookId = $request->book_id ?? null;
    //         $documentDate = $request->document_date ?? null;
    //         $user = Helper::getAuthenticatedUser();
    //         BomUpload::where('created_by', $user?->auth_user_id)->delete();
    //         $parentUrl = request()->segments()[0];
    //         $moduleTyle = $parentUrl == 'quotation-bom' ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS : ConstantHelper::BOM_SERVICE_ALIAS;
    //         Excel::import(new BomImportData($bookId,$documentDate,$moduleTyle), $request->file('attachment'));
    //         $uploads = BomUpload::where('migrate_status', 0)
    //                 ->where('created_by', $user->auth_user_id)
    //                 ->get();
    //         $grouped = $uploads->groupBy(function ($item) use($moduleTyle) {
    //             if($moduleTyle == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
    //                 return $item->product_item_id . '-' . $item->product_item_code . '-' . $item->uom_id . '-' . $item->customer_id;
    //             } else {
    //                 return $item->product_item_id . '-' . $item->product_item_code . '-' . $item->uom_id;
    //             }
    //         })->map(function ($group) {
    //             $first = $group->first();
    //             return [
    //                 'type' => $first->type,
    //                 'production_type' => $first->production_type,
    //                 'customizable' => $first->customizable,
    //                 'production_route_id' => $first->production_route_id,
    //                 'product_item_id' => $first->product_item_id,
    //                 'product_item_code' => $first->product_item_code,
    //                 'uom_id' => $first->uom_id,
    //                 'uom_code' => $first->uom_code,
    //                 'customer_id' => $first->customer_id ?? null,
    //                 'product_attributes' => $first->product_attributes ?? [],
    //                 'items' => $group->map(function ($item) {
    //                     return [
    //                         'item_id' => $item->item_id,
    //                         'item_code' => $item->item_code,
    //                         'item_uom_id' => $item->item_uom_id,
    //                         'item_uom_code' => $item->item_uom_code,
    //                         'consumption_qty' => $item->consumption_qty,
    //                         'consumption_per_unit' => $item->consumption_per_unit,
    //                         'pieces' => $item->pieces,
    //                         'std_qty' => $item->std_qty,
    //                         'calculated_consumption' => $item->calculated_consumption,
    //                         'cost_per_unit' => $item->cost_per_unit,
    //                         'item_attributes' => $item->item_attributes ?? [],
    //                         'station_id' => $item->station_id,
    //                         'station_name' => $item->station_name,
    //                         'section_id' => $item->section_id,
    //                         'section_name' => $item->section_name,
    //                         'sub_section_name' => $item->sub_section_name,
    //                         'sub_section_id' => $item->sub_section_id,
    //                         'vendor_id' => $item->vendor_id,
    //                         'reason' => $item->reason,
    //                         'remark' => $item->remark,
    //                     ];
    //                 })->values()
    //             ];
    //         })->values();
    //         # Bom Header save
    //         $organization = Organization::where('id', $user->organization_id)->first();
    //         foreach($grouped as $groupedData) {
    //             $bomExists = Bom::where('item_id', $groupedData['product_item_id'])
    //                         ->where('type', $moduleTyle)
    //                         ->where(function ($query) use ($groupedData,$moduleTyle) {
    //                             if ($moduleTyle == 'qbom') {
    //                                 $query->where('customer_id', $groupedData['customer_id'] ?? null);
    //                             }
    //                         })
    //                         ->where('status', ConstantHelper::ACTIVE)
    //                         ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_SUBMITTED)
    //                         ->first();
    //             if ($bomExists) {
    //                 BomUpload::where('migrate_status', 0)
    //                     ->where('created_by', $user->auth_user_id)
    //                     ->where('product_item_id', $groupedData['product_item_id'])
    //                     ->get()
    //                     ->each(function ($row) use ($bomExists) {
    //                         $reasons = $row->reason ?? [];
    //                         $reasons[] = 'Bom Already Created';
    //                         $row->reason = array_unique($reasons);
    //                         $row->bom_id = $bomExists->id;
    //                         $row->save();
    //                     });
    //                 continue;
    //             }
    //             $reasonCount = $groupedData['items']->sum(function ($item) {
    //                 return is_array($item['reason']) ? count($item['reason']) : 0;
    //             });
    //             if($reasonCount) {
    //                 continue;
    //             }
    //             $bom = new Bom;
    //             $bom->type = $moduleTyle;
    //             $bom->bom_type = ConstantHelper::FIXED;
    //             $bom->organization_id = $organization->id;
    //             $bom->group_id = $organization->group_id;
    //             $bom->company_id = $organization->company_id;
    //             $bom->uom_id = $groupedData['uom_id'] ?? null;
    //             $bom->production_type = $groupedData['production_type'] ?? 'In-house';
    //             $bom->item_id = $groupedData['product_item_id'] ?? null;
    //             $bom->item_code = $groupedData['product_item_code'] ?? null;
    //             $bom->item_name = $groupedData['product_item_name'] ?? null;
    //             $bom->revision_number = 0;
    //             $bom->production_route_id = $groupedData['production_route_id'] ?? null;
    //             $bom->customer_id = $groupedData['customer_id'] ?? null;
    //             $bom->customizable = strtolower($groupedData['customizable']) ?? 'no';
    //             // $bom->remarks = $request->remarks;
    //             # Extra Column
    //             // $document_number = $request->document_number ?? null;
    //             $document_number = null;
    //             /**/
    //             $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);
    //             // if (!isset($numberPatternData)) {
    //             //     DB::rollBack();
    //             //     return response()->json([
    //             //         'message' => "Invalid Book",
    //             //         'error' => "",
    //             //     ], 422);
    //             // }
    //             $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : $document_number;
    //             // $regeneratedDocExist = Bom::where('book_id',$request->book_id)
    //             //     ->where('document_number',$document_number)->first();
    //             // //Again check regenerated doc no
    //             // if (isset($regeneratedDocExist)) {
    //             //     DB::rollBack();
    //             //     return response()->json([
    //             //         'message' => ConstantHelper::DUPLICATE_DOCUMENT_NUMBER,
    //             //         'error' => "",
    //             //     ], 422);
    //             // }
    //             $bom->doc_number_type = $numberPatternData['type'];
    //             $bom->doc_reset_pattern = $numberPatternData['reset_pattern'];
    //             $bom->doc_prefix = $numberPatternData['prefix'];
    //             $bom->doc_suffix = $numberPatternData['suffix'];
    //             $bom->doc_no = $numberPatternData['doc_no'];
    //             $bom->book_id = $request->book_id;
    //             $bom->book_code = $request->book_code;
    //             $bom->document_number = $document_number;
    //             $bom->document_date = $request->document_date ?? now();
    //             $bom->save();
    //             # Save header attribute
    //             if(count($groupedData['product_attributes'])) {
    //                 foreach($groupedData['product_attributes'] as $productAttribute) {
    //                     $bomAttr = new BomAttribute;
    //                     $bomAttr->bom_id = $bom->id;
    //                     $bomAttr->item_attribute_id = $productAttribute['item_attribute_id'] ?? null;
    //                     $bomAttr->item_id = $bom?->item?->id;
    //                     $bomAttr->type = 'H';
    //                     $bomAttr->item_code = $bom?->item?->item_code;
    //                     $bomAttr->attribute_name = $productAttribute['attribute_name_id'] ?? null;
    //                     $bomAttr->attribute_value = $productAttribute['attribute_value_id'] ?? null;
    //                     $bomAttr->save();
    //                 }
    //             }
    //             if(count($groupedData['items'])) {
    //                 foreach($groupedData['items'] as $groupedDataItem) {
    //                     $bomDetail = new BomDetail;
    //                     $bomDetail->bom_id = $bom->id;
    //                     $bomDetail->item_id = $groupedDataItem['item_id'] ?? null;
    //                     $bomDetail->item_code = $groupedDataItem['item_code'] ?? null;
    //                     $bomDetail->uom_id = $groupedDataItem['item_uom_id'] ?? null;
    //                     $bomDetail->qty = $groupedDataItem['calculated_consumption'] > 0 ? $groupedDataItem['calculated_consumption'] : $groupedDataItem['consumption_qty'];
    //                     $bomDetail->item_cost = $groupedDataItem['cost_per_unit'] ?? 0.00;
    //                     $bomDetail->item_value = floatval($groupedDataItem['consumption_qty']) * floatval($groupedDataItem['cost_per_unit']);
    //                     $bomDetail->total_amount = floatval($groupedDataItem['consumption_qty']) * floatval($groupedDataItem['cost_per_unit']);
    //                     $bomDetail->sub_section_id = $groupedDataItem['sub_section_id'] ?? null;
    //                     $bomDetail->sub_section_name = $groupedDataItem['sub_section_name'] ?? null;
    //                     $bomDetail->section_id = $groupedDataItem['section_id'] ?? null;
    //                     $bomDetail->section_name = $groupedDataItem['section_name'] ?? null;
    //                     $bomDetail->station_id = $groupedDataItem['station_id'] ?? null;
    //                     $bomDetail->station_name = $groupedDataItem['station_name'] ?? null;
    //                     $bomDetail->vendor_id = $groupedDataItem['vendor_id'] ?? null;
    //                     $bomDetail->remark = $groupedDataItem['remark'] ?? null;
    //                     $bomDetail->save();
    //                     if($groupedDataItem['calculated_consumption']) {
    //                             $normData = [
    //                                 'bom_id' => $bom->id,
    //                                 'bom_detail_id' => $bomDetail->id,
    //                             ];
    //                             $updateData = [
    //                                 'qty_per_unit' => $groupedDataItem['consumption_per_unit'] ?? 0.00,
    //                                 'total_qty' => $groupedDataItem['pieces'] ?? 0.00,
    //                                 'std_qty' => $groupedDataItem['std_qty'] ?? 0.00,
    //                                 'created_at' => now(),
    //                                 'updated_at' => now(),
    //                             ];
    //                             if($updateData['qty_per_unit'] && $updateData['total_qty'] && $updateData['std_qty']){
    //                                 BomNormsCalculation::updateOrCreate($normData, $updateData);
    //                             }
    //                     }
    //                     #Save component Attr
    //                     if(count($groupedDataItem['item_attributes'])) {
    //                         foreach($groupedDataItem['item_attributes'] as $itemAttribute) {
    //                             $bomAttr = new BomAttribute;
    //                             $bomAttr->bom_id = $bom->id;
    //                             $bomAttr->bom_detail_id = $bomDetail->id;
    //                             $bomAttr->item_attribute_id = $itemAttribute['item_attribute_id'] ?? null;
    //                             $bomAttr->type = 'D';
    //                             $bomAttr->item_code = $groupedDataItem['item_code'];
    //                             $bomAttr->item_id = $groupedDataItem['item_id'];
    //                             $bomAttr->attribute_name = $itemAttribute['attribute_name_id'] ?? null;
    //                             $bomAttr->attribute_value = $itemAttribute['attribute_value_id'] ?? null;
    //                             $bomAttr->save();
    //                         }
    //                     }
    //                 }
    //             }
    //             /*Update Bom header*/
    //             $bom->total_item_value = $bom->bomItems()->sum('item_value') ?? 0.00;
    //             $bom->save();
    //             /*Create document submit log*/
    //             $modelName = get_class($bom);
    //             $totalValue = $bom->total_value ?? 0;
    //             if ($request->document_status == ConstantHelper::SUBMITTED) {
    //                 $bookId = $bom->book_id;
    //                 $docId = $bom->id;
    //                 $remarks = $bom->remarks;
    //                 $attachments = $request->file('attachment');
    //                 $currentLevel = $bom->approval_level ?? 1;
    //                 $revisionNumber = $bom->revision_number ?? 0;
    //                 $actionType = 'submit';
    //                 $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType, $totalValue, $modelName);
    //             }
    //             if ($request->document_status == ConstantHelper::SUBMITTED) {
    //                 $bom->document_status = $approveDocument['approvalStatus'] ?? $request->document_status;
    //             } else {
    //                 $bom->document_status = $request->document_status ?? ConstantHelper::DRAFT;
    //             }
    //             $bom->save();
    //             if($bom) {
    //                 BomUpload::where('migrate_status', 0)
    //                         ->where('created_by', $user->auth_user_id)
    //                         ->where('product_item_id', $groupedData['product_item_id'])
    //                         ->get()
    //                         ->each(function ($row) use ($bom) {
    //                             $row->migrate_status = true;
    //                             $row->bom_id = $bom->id;
    //                             $row->save();
    //                         });
    //             }
    //         }
    //         DB::commit();
    //         $errorRows = BomUpload::where('created_by', $user->auth_user_id)
    //                     ->where('migrate_status', 0)
    //                     ->get();
    //         if(count($errorRows)) {
    //             if(isset($bom) && $bom) {
    //                 return response()->json([
    //                     'message' => 'Some records were imported successfully, but some had issues. Please downloaded error file to review them.',
    //                     'data' => @$bom,
    //                     'redirect_url' => route('bill.of.material.import.error')
    //                 ]);
    //             } else {
    //                 return response()->json([
    //                     'message' => 'No bom import, Please downloaded error file to review them.',
    //                     'data' => @$bom,
    //                     'redirect_url' => route('bill.of.material.import.error')
    //                 ]);
    //             }

    //         }
    //         return response()->json([
    //             'message' => 'Record imported successfully',
    //             'data' => @$bom
    //         ]);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Error occurred while importing the record.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function importSave(BomImportRequest $request)
    {
        $user = Helper::getAuthenticatedUser();

        try {
            // 1) Clean previous temp rows for this user (fast, outside any transaction)
            BomUpload::where('created_by', $user?->auth_user_id)->delete();

            // 2) Import Excel (pure I/O + inserts into BomUpload; keep out of transaction)
            $bookId       = $request->book_id ?? null;
            $documentDate = $request->document_date ?? null;
            $parentUrl    = request()->segments()[0] ?? '';
            $moduleTyle   = $parentUrl === 'quotation-bom'
                ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS
                : ConstantHelper::BOM_SERVICE_ALIAS;

            Excel::import(new BomImportData($bookId, $documentDate, $moduleTyle), $request->file('attachment'));

            // 3) Load uploaded rows for this user that still need migration
            $uploads = BomUpload::where('migrate_status', 0)
                ->where('created_by', $user->auth_user_id)
                ->get();

            if ($uploads->isEmpty()) {
                return response()->json([
                    'message' => 'No rows to import.',
                    'data'    => null,
                ]);
            }

            // 4) Group rows by BOM header signature (keeps grouping in memory; no locks)
            $grouped = $uploads->groupBy(function ($item) use ($moduleTyle) {
                if ($moduleTyle == ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
                    return implode('-', [
                        $item->product_item_id,
                        $item->product_item_code,
                        $item->uom_id,
                        $item->customer_id,
                    ]);
                }
                return implode('-', [
                    $item->product_item_id,
                    $item->product_item_code,
                    $item->uom_id,
                ]);
            })->map(function ($group) {
                $first = $group->first();

                return [
                    'type'               => $first->type,
                    'production_type'    => $first->production_type,
                    'customizable'       => $first->customizable,
                    'production_route_id'=> $first->production_route_id,
                    'product_item_id'    => $first->product_item_id,
                    'product_item_code'  => $first->product_item_code,
                    'product_item_name'  => $first->product_item_name ?? null,
                    'uom_id'             => $first->uom_id,
                    'uom_code'           => $first->uom_code,
                    'customer_id'        => $first->customer_id ?? null,
                    'product_attributes' => $first->product_attributes ?? [],
                    'items'              => $group->map(function ($item) {
                        return [
                            'item_id'                 => $item->item_id,
                            'item_code'               => $item->item_code,
                            'item_uom_id'             => $item->item_uom_id,
                            'item_uom_code'           => $item->item_uom_code,
                            'consumption_qty'         => $item->consumption_qty,
                            'consumption_per_unit'    => $item->consumption_per_unit,
                            'pieces'                  => $item->pieces,
                            'std_qty'                 => $item->std_qty,
                            'calculated_consumption'  => $item->calculated_consumption,
                            'cost_per_unit'           => $item->cost_per_unit,
                            'item_attributes'         => $item->item_attributes ?? [],
                            'station_id'              => $item->station_id,
                            'station_name'            => $item->station_name,
                            'section_id'              => $item->section_id,
                            'section_name'            => $item->section_name,
                            'sub_section_name'        => $item->sub_section_name,
                            'sub_section_id'          => $item->sub_section_id,
                            'vendor_id'               => $item->vendor_id,
                            'reason'                  => $item->reason,
                            'remark'                  => $item->remark,
                        ];
                    })->values(),
                ];
            })->values();

            $organization = Organization::where('id', $user->organization_id)->first();
            $lastBom      = null;
            $hadErrors    = false;

            foreach ($grouped as $groupedData) {

                // Skip early if any row in this group has reasons (don’t even open a transaction)
                $reasonCount = collect($groupedData['items'])->sum(function ($item) {
                    return is_array($item['reason']) ? count($item['reason']) : 0;
                });
                if ($reasonCount > 0) {
                    $hadErrors = true;
                    continue;
                }

                // **Short, per-BOM transaction** with retry on deadlock/lock wait
                $result = DB::transaction(function () use (
                    $request,
                    $moduleTyle,
                    $groupedData,
                    $organization
                ) {
                    // Check for existing active BOM (keep read quick)
                    $bomExistsQuery = Bom::where('item_id', $groupedData['product_item_id'])
                        ->where('type', $moduleTyle)
                        ->where('status', ConstantHelper::ACTIVE)
                        ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_SUBMITTED);

                    if ($moduleTyle === 'qbom') {
                        $bomExistsQuery->where('customer_id', $groupedData['customer_id'] ?? null);
                    }

                    $bomExists = $bomExistsQuery->first();

                    if ($bomExists) {
                        // Mark uploads with reason in one write OUTSIDE of loops
                        BomUpload::where('migrate_status', 0)
                            ->where('created_by', Helper::getAuthenticatedUser()->auth_user_id)
                            ->where('product_item_id', $groupedData['product_item_id'])
                            ->update([
                                'reason' => DB::raw("JSON_ARRAY_APPEND(COALESCE(reason, JSON_ARRAY()), '$', 'Bom Already Created')"),
                                'bom_id' => $bomExists->id,
                            ]);

                        // Tell caller this group produced an error (so the caller can set hadErrors)
                        return ['bom' => null, 'skipped' => true];
                    }

                    // Generate document number (fast, deterministic)
                    $numberPatternData = Helper::generateDocumentNumberNew($request->book_id, $request->document_date);

                    // Create BOM Header (single insert)
                    $bom = new Bom();
                    $bom->type               = $moduleTyle;
                    $bom->bom_type           = ConstantHelper::FIXED;
                    $bom->organization_id    = $organization->id;
                    $bom->group_id           = $organization->group_id;
                    $bom->company_id         = $organization->company_id;
                    $bom->uom_id             = $groupedData['uom_id'] ?? null;
                    $bom->production_type    = $groupedData['production_type'] ?? 'In-house';
                    $bom->item_id            = $groupedData['product_item_id'] ?? null;
                    $bom->item_code          = $groupedData['product_item_code'] ?? null;
                    $bom->item_name          = $groupedData['product_item_name'] ?? null;
                    $bom->revision_number    = 0;
                    $bom->production_route_id= $groupedData['production_route_id'] ?? null;
                    $bom->customer_id        = $groupedData['customer_id'] ?? null;
                    $bom->customizable       = strtolower($groupedData['customizable'] ?? 'no');
                    $bom->doc_number_type    = $numberPatternData['type'] ?? null;
                    $bom->doc_reset_pattern  = $numberPatternData['reset_pattern'] ?? null;
                    $bom->doc_prefix         = $numberPatternData['prefix'] ?? null;
                    $bom->doc_suffix         = $numberPatternData['suffix'] ?? null;
                    $bom->doc_no             = $numberPatternData['doc_no'] ?? null;
                    $bom->book_id            = $request->book_id;
                    $bom->book_code          = $request->book_code;
                    $bom->document_number    = $numberPatternData['document_number'] ?? null;
                    $bom->document_date      = $request->document_date ?? now();
                    $bom->save();

                    // Header attributes (batch insert friendly, but simple loop is fine)
                    if (!empty($groupedData['product_attributes'])) {
                        foreach ($groupedData['product_attributes'] as $productAttribute) {
                            $bomAttr = new BomAttribute();
                            $bomAttr->bom_id            = $bom->id;
                            $bomAttr->item_attribute_id = $productAttribute['item_attribute_id'] ?? null;
                            $bomAttr->item_id           = $bom?->item?->id;
                            $bomAttr->type              = 'H';
                            $bomAttr->item_code         = $bom?->item?->item_code;
                            $bomAttr->attribute_name    = $productAttribute['attribute_name_id'] ?? null;
                            $bomAttr->attribute_value   = $productAttribute['attribute_value_id'] ?? null;
                            $bomAttr->save();
                        }
                    }

                    // Details + attributes + norms (keep writes tight)
                    $normUpserts = [];
                    foreach ($groupedData['items'] as $groupedDataItem) {
                        $bomDetail = new BomDetail();
                        $bomDetail->bom_id        = $bom->id;
                        $bomDetail->item_id       = $groupedDataItem['item_id'] ?? null;
                        $bomDetail->item_code     = $groupedDataItem['item_code'] ?? null;
                        $bomDetail->uom_id        = $groupedDataItem['item_uom_id'] ?? null;
                        $consumptionQty           = (float)($groupedDataItem['consumption_qty'] ?? 0);
                        $calcConsumption          = (float)($groupedDataItem['calculated_consumption'] ?? 0);
                        $costPerUnit              = (float)($groupedDataItem['cost_per_unit'] ?? 0);
                        $bomDetail->qty           = $calcConsumption > 0 ? $calcConsumption : $consumptionQty;
                        $bomDetail->item_cost     = $costPerUnit;
                        $bomDetail->item_value    = $consumptionQty * $costPerUnit;
                        $bomDetail->total_amount  = $consumptionQty * $costPerUnit;
                        $bomDetail->sub_section_id= $groupedDataItem['sub_section_id'] ?? null;
                        $bomDetail->sub_section_name = $groupedDataItem['sub_section_name'] ?? null;
                        $bomDetail->section_id    = $groupedDataItem['section_id'] ?? null;
                        $bomDetail->section_name  = $groupedDataItem['section_name'] ?? null;
                        $bomDetail->station_id    = $groupedDataItem['station_id'] ?? null;
                        $bomDetail->station_name  = $groupedDataItem['station_name'] ?? null;
                        $bomDetail->vendor_id     = $groupedDataItem['vendor_id'] ?? null;
                        $bomDetail->remark        = $groupedDataItem['remark'] ?? null;
                        $bomDetail->save();

                        if (!empty($groupedDataItem['calculated_consumption'])) {
                            $normUpserts[] = [
                                'bom_id'        => $bom->id,
                                'bom_detail_id' => $bomDetail->id,
                                'qty_per_unit'  => (float)($groupedDataItem['consumption_per_unit'] ?? 0.00),
                                'total_qty'     => (float)($groupedDataItem['pieces'] ?? 0.00),
                                'std_qty'       => (float)($groupedDataItem['std_qty'] ?? 0.00),
                                'created_at'    => now(),
                                'updated_at'    => now(),
                            ];
                        }

                        if (!empty($groupedDataItem['item_attributes'])) {
                            foreach ($groupedDataItem['item_attributes'] as $itemAttribute) {
                                $dAttr = new BomAttribute();
                                $dAttr->bom_id            = $bom->id;
                                $dAttr->bom_detail_id     = $bomDetail->id;
                                $dAttr->item_attribute_id = $itemAttribute['item_attribute_id'] ?? null;
                                $dAttr->type              = 'D';
                                $dAttr->item_code         = $groupedDataItem['item_code'] ?? null;
                                $dAttr->item_id           = $groupedDataItem['item_id'] ?? null;
                                $dAttr->attribute_name    = $itemAttribute['attribute_name_id'] ?? null;
                                $dAttr->attribute_value   = $itemAttribute['attribute_value_id'] ?? null;
                                $dAttr->save();
                            }
                        }
                    }

                    // Bulk upsert norms (one statement, avoids row-by-row lock churn)
                    if (!empty($normUpserts)) {
                        BomNormsCalculation::upsert(
                            $normUpserts,
                            ['bom_id', 'bom_detail_id'], // unique-by columns
                            ['qty_per_unit', 'total_qty', 'std_qty', 'updated_at']
                        );
                    }

                    // Update totals quickly
                    $bom->total_item_value = $bom->bomItems()->sum('item_value') ?? 0.00;
                    $bom->save();

                    // Submit/approval (writes are short)
                    $modelName    = get_class($bom);
                    $totalValue   = $bom->total_value ?? 0;
                    if ($request->document_status == ConstantHelper::SUBMITTED) {
                        $approveDocument = Helper::approveDocument(
                            $bom->book_id,
                            $bom->id,
                            $bom->revision_number ?? 0,
                            $bom->remarks,
                            $request->file('attachment'),
                            $bom->approval_level ?? 1,
                            'submit',
                            $totalValue,
                            $modelName
                        );
                        $bom->document_status = $approveDocument['approvalStatus'] ?? $request->document_status;
                    } else {
                        $bom->document_status = $request->document_status ?? ConstantHelper::DRAFT;
                    }
                    $bom->save();

                    // Mark the group’s uploads as migrated in ONE update
                    BomUpload::where('migrate_status', 0)
                        ->where('created_by', Helper::getAuthenticatedUser()->auth_user_id)
                        ->where('product_item_id', $groupedData['product_item_id'])
                        ->update([
                            'migrate_status' => true,
                            'bom_id'         => $bom->id,
                        ]);

                    return ['bom' => $bom, 'skipped' => false];
                }, 3); // retry up to 3 times on deadlock/lock wait

                if ($result['skipped']) {
                    $hadErrors = true;
                } else {
                    $lastBom = $result['bom'];
                }
            }

            // 5) If any rows in BomUpload still have migrate_status = 0, surface the error file route
            $errorRows = BomUpload::where('created_by', $user->auth_user_id)
                ->where('migrate_status', 0)
                ->count();

            if ($errorRows > 0 || $hadErrors) {
                return response()->json([
                    'message'      => $lastBom
                        ? 'Some records were imported successfully, but some had issues. Please download the error file to review them.'
                        : 'No BOMs imported. Please download the error file to review issues.',
                    'data'         => $lastBom,
                    'redirect_url' => route('bill.of.material.import.error'),
                ]);
            }

            return response()->json([
                'message' => 'Record(s) imported successfully',
                'data'    => $lastBom,
            ]);
        } catch (\Throwable $e) {
            // No outer transaction to roll back; we kept it per-BOM
            return response()->json([
                'message' => 'Error occurred while importing the record.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    # Download import error
    public function importError(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $errorRows = BomUpload::where('created_by', $user->auth_user_id)
                    ->where('migrate_status', 0)
                    ->get();
        if ($errorRows->isEmpty()) {
            return redirect()->back()->with('message', 'No import errors found.');
        }
        $fileName = 'BOM_IMPORT_ERRORS_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new BomImportErrorExport($errorRows), $fileName);
    }
}
