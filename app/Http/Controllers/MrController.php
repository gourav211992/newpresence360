<?php

namespace App\Http\Controllers;

use DB;
use PDF;
use Auth;
use Illuminate\Http\Request;

use App\Helpers\Helper;
use App\Helpers\NumberHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\CurrencyHelper;

use App\Http\Requests\MrRequest;
use App\Http\Controllers\Controller;

use App\Models\Address;
use App\Models\AttributeGroup;
use App\Models\Book;
use App\Models\Hsn;
use App\Models\Item;
use App\Models\NumberPattern;
use App\Models\Organization;

use App\Models\MaterialRequest\MrTed;
use App\Models\MaterialRequest\MrItem;
use App\Models\MaterialRequest\MrHeader;
use App\Models\MaterialRequest\MrDetail;
use App\Models\MaterialRequest\MrItemAttribute;
use App\Models\Unit;
use Illuminate\Support\Facades\DB as FacadesDB;

class MrController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $records = MrHeader::where('organization_id', $user->organization_id)
                    ->where('company_id', $organization->company_id)
                    ->latest()
                    ->get();
        return view('procurement.material-request.index', compact('records'));
    }

    // # Mr create
    public function create()
    {
        $user=Auth::user();
        $serviceAlias = ConstantHelper::MR_SERVICE_ALIAS;
        $books = Helper::getBookSeries($serviceAlias)->get();

        $stores = Helper::getStoreSeries($serviceAlias)->get();

        return view('procurement.material-request.create', [
            'books'=> $books,
            'stores'=>$stores
        ]);
    }

    # Add item row
    public function addItemRow(Request $request)
    {
        $item = json_decode($request->item,true) ?? [];
        $componentItem = json_decode($request->component_item,true) ?? [];
        /*Check last tr in table mandatory*/
        if(isset($componentItem['attr_require']) && isset($componentItem['item_id']) && $componentItem['row_length']) {
            if (($componentItem['attr_require'] == true || !$componentItem['item_id']) && $componentItem['row_length'] != 0) {
                return response()->json(['data' => ['html' => ''], 'status' => 422, 'message' => 'Please fill all component details before adding new row more!']);
            }
        }
        $rowCount = intval($request->count) == 0 ? 1 : intval($request->count) + 1;
        $html = view('procurement.material-request.partials.item-row',compact('rowCount'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On select row get item detail
    public function getItemDetail(Request $request)
    {
        $selectedAttr = json_decode($request->selectedAttr,200) ?? [];
        $delivery = json_decode($request->delivery,200) ?? [];
        $item = Item::find($request->item_id ?? null);

        $uomId = $request->uom_id ?? null;
        $qty = intval($request->qty) ?? 0;

        $uomName = $item->uom->name ?? 'NA';
        if($item->uom_id == $uomId) {
        } else {
            $alUom = $item->alternateUOMs()->where('uom_id', $uomId)->first();
            $qty = $alUom?->conversion_to_inventory * $qty;
        }

        $specifications = $item->specifications()->whereNotNull('value')->get();

        $remark = $request->remark ?? null;
        $delivery = isset($delivery) ? $delivery  : null;
        $html = view('procurement.material-request.partials.comp-item-detail',compact('item','selectedAttr','remark','uomName','qty','delivery','specifications'))->render();
        return response()->json(['data' => ['html' => $html], 'status' => 200, 'message' => 'fetched.']);
    }

    # On change item attribute
    public function getItemAttribute(Request $request)
    {
        $attributeGroups = AttributeGroup::with('attributes')->where('status', ConstantHelper::ACTIVE)->get();
        $rowCount = intval($request->rowCount) ?? 1;
        $item = Item::find($request->item_id);
        $selectedAttr = $request->selectedAttr ? json_decode($request->selectedAttr,true) : [];
        $html = view('procurement.material-request.partials.comp-attribute',compact('item','attributeGroups','rowCount','selectedAttr'))->render();
        $hiddenHtml = '';
        foreach ($item->itemAttributes as $attribute) {
                $selected = '';
                foreach ($attribute->attributes() as $value){
                    if (in_array($value->id, $selectedAttr)){
                        $selected = $value->id;
                    }
                }
            $hiddenHtml .= "<input type='hidden' name='components[$rowCount][attr_group_id][$attribute->attribute_group_id][attr_name]' value=$selected>";
        }
        return response()->json(['data' => ['attr' => $item->itemAttributes->count(),'html' => $html, 'hiddenHtml' => $hiddenHtml], 'status' => 200, 'message' => 'fetched.']);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $organization = Organization::where('id', $user->organization_id)->first();
            $totalItemValue = 0.00;
            $totalDiscValue = 0.00;
            $totalExpValue = 0.00;
            $totalItemLevelDiscValue = 0.00;
            $totalAmount = 0.00;

            $organizationId = $organization ?-> id ?? null;
            $groupId = $organization ?-> group_id ?? null;
            $companyId = $organization ?-> company_id ?? null;

            // $currencyExchangeData = CurrencyHelper::getCurrencyExchangeRates($request -> currency_id, $request -> document_date);
            // if ($currencyExchangeData['status'] == false) {
            //     return response()->json([
            //         'message' => $currencyExchangeData['message']
            //     ], 422);
            // }
            # Mr Header save
            $mr = new MrHeader();
            $mr->fill($request->all());
            $mr->organization_id = $organization->id;
            $mr->group_id = $organization->group_id;
            $mr->company_id = $organization->company_id;
            $mr->book_code = $request->book_code;
            $mr->book_id = $request->book_id;
            $mr->store_id = $request->store_id;
            $mr->store_code = $request->store_code;
            $mr->document_number = $request->document_number;
            $mr->document_date = $request->document_date;
            $mr->revision_number = 0;
            $mr->document_status = $request->document_status;
            $mr->remark = $request->remarks ?? null;
            // $mr->org_currency_id = $currencyExchangeData['data']['org_currency_id'];
            // $mr->org_currency_code = $currencyExchangeData['data']['org_currency_code'];
            // $mr->org_currency_exg_rate = $currencyExchangeData['data']['org_currency_exg_rate'];
            // $mr->comp_currency_id = $currencyExchangeData['data']['comp_currency_id'];
            // $mr->comp_currency_code = $currencyExchangeData['data']['comp_currency_code'];
            // $mr->comp_currency_exg_rate = $currencyExchangeData['data']['comp_currency_exg_rate'];
            // $mr->group_currency_id = $currencyExchangeData['data']['group_currency_id'];
            // $mr->group_currency_code = $currencyExchangeData['data']['group_currency_code'];
            // $mr->group_currency_exg_rate = $currencyExchangeData['data']['group_currency_exg_rate'];
            $mr->save();
            if (isset($request->all()['components'])) {
                foreach($request->all()['components'] as $c_key => $component) {
                    $item = Item::find($component['item_id'] ?? null);
                    # Material Request Detail Save
                    $mrDetail = new MrDetail();
                    // dd($request->all());
                    $mrDetail->header_id = $mr->id;
                    $mrDetail->item_id = $component['item_id'] ?? null;
                    $mrDetail->item_code = $component['item_code'] ?? null;
                    $mrDetail->item_name = $component['item_name'] ?? null;
                    $mrDetail->hsn_id = $component['hsn_id'] ?? null;
                    $mrDetail->hsn_code = $component['hsn_code'] ?? null;
                    $mrDetail->uom_id = $item->uom_id ?? null;
                    $mrDetail->uom_code = $item->uom->alias ?? null;
                    $mrDetail->quantity = $component['quantity'] ?? 0.00;
                    $mrDetail->inventory_uom_id = $item->uom_id ?? null;
                    $mrDetail->inventory_uom_code = $item->uom->name ?? null;
                    if(@$component['uom_id'] == @$item->uom_id) {
                        $mrDetail->inventory_uom_qty = $component['quantity'] ?? 0.00;
                    } else {
                        $alUom = @$item->alternateUOMs()->where('uom_id',$component['uom_id'])->first();
                        if($alUom) {
                            $mrDetail->inventory_uom_qty = intval($component['quantity']) * $alUom->conversion_to_inventory;
                        }
                    }
                    $mrDetail->rate = $component['rate'] ?? 0.00;
                    $mrDetail->discount_amount = $component['discount_amount'] ?? 0.00;
                    $mrDetail->header_discount_amount = $component['discount_amount_header'] ?? 0.00;
                    $mrDetail->header_exp_amount = $component['exp_amount_header'] ?? 0.00;
                    $mrDetail->remark = $component['remark'] ?? null;
                    $mrDetail->save();

                    $totalItemValue = $totalItemValue + ($mrDetail->quantity*$mrDetail->rate);
                    $totalDiscValue = $totalDiscValue + ($mrDetail->discount_amount + $mrDetail->header_discount_amount);
                    $totalExpValue = $totalExpValue + $mrDetail->header_exp_amount;
                    // dd($totalItemValue,$totalDiscValue,$totalExpValue);
                    #Save component Attr
                    foreach($mrDetail->item->itemAttributes as $itemAttribute) {
                        if (isset($component['attr_group_id'][$itemAttribute->attribute_group_id])) {
                        $mrAttr = new MrItemAttribute;
                        $mrAttrName = @$component['attr_group_id'][$itemAttribute->attribute_group_id]['attr_name'];
                        $mrAttr->header_id = $mr->id;
                        $mrAttr->detail_id = $mrDetail->id;
                        $mrAttr->item_attribute_id = $itemAttribute->id;
                        $mrAttr->item_code = $component['item_code'] ?? null;
                        $mrAttr->attr_name = $itemAttribute->attribute_group_id;
                        $mrAttr->attr_value = $mrAttrName ?? null;
                        $mrAttr->save();
                        }
                    }

                    /*Item Level Discount Save*/
                    if(isset($component['discounts'])) {
                        foreach($component['discounts'] as $dis) {
                            $ted = new MrTed;
                            $ted->header_id = $mr->id;
                            $ted->detail_id = $mrDetail->id;
                            $ted->ted_type = 'Discount';
                            $ted->ted_level = 'D';
                            $ted->ted_id = null;
                            $ted->ted_name = $dis['dis_name'];
                            $ted->ted_code = $dis['dis_name'];
                            $ted->assesment_amount = $mrDetail->assessment_amount;
                            $ted->ted_percentage = $dis['dis_perc'] ?? 0.00;
                            $ted->ted_amount = $dis['dis_amount'] ?? 0.00;
                            $ted->applicability_type = 'Deduction';
                            $ted->save();
                            $totalItemLevelDiscValue = $totalItemLevelDiscValue+$dis['dis_amount'];
                        }
                    }
                }
            } else {
                DB::rollBack();
                return response()->json([
                        'message' => 'Please add atleast one row in component table.',
                        'error' => "",
                    ], 422);
            }


            # Save attachment file
            // if ($request->hasFile('attachment')) {
            //     $attachments = $request->file('attachment');
            //     foreach ($attachments as $key => $attachment) {
            //         $levelName = "attachment.$key";
            //         $mr->addMediaFromRequest($levelName)->toMediaCollection('attachment');
            //     }
            // }

            /*Header level save discount*/
            if(isset($request->all()['disc_summary'])) {
                foreach($request->all()['disc_summary'] as $dis) {
                    $ted = new MrTed;
                    $ted->header_id = $mr->id;
                    $ted->detail_id = null;
                    $ted->ted_type = 'Discount';
                    $ted->ted_level = 'H';
                    $ted->ted_id = null;
                    $ted->ted_name = @$dis['d_name'];
                    $ted->ted_code = @$dis['d_name'];
                    $ted->assesment_amount = $totalItemValue-$totalItemLevelDiscValue;
                    $ted->ted_percentage = @$dis['d_perc'] ?? 0.00;
                    $ted->ted_amount = @$dis['d_amnt'] ?? 0.00;
                    $ted->applicability_type = 'Deduction';
                    $ted->save();
                }
            }

            /*Header level save expense*/
            if(isset($request->all()['exp_summary'])) {
                foreach($request->all()['exp_summary'] as $dis) {
                    $ted = new MrTed;
                    $ted->header_id = $mr->id;
                    $ted->detail_id = null;
                    $ted->ted_type = 'MR';
                    $ted->ted_level = 'H';
                    $ted->ted_id = null;
                    $ted->ted_name = $dis['e_name'];
                    $ted->assesment_amount = $totalItemValue/*-$totalItemLevelDiscValue*/;
                    $ted->ted_percentage = @$dis['e_perc'] ?? 0.00;
                    $ted->ted_amount = @$dis['e_amnt'] ?? 0.00;
                    $ted->applicability_type = 'Collection';
                    $ted->save();
                }
            }
            /*Update Calculation*/
            // dd($totalItemValue, $totalDiscValue, ($totalItemValue - $totalDiscValue), $totalExpValue, (($totalItemValue - $totalDiscValue) + ($totalExpValue)));
            $mr->total_item_amount = $totalItemValue;
            $mr->total_discount = $totalDiscValue;
            $mr->taxable_amount = ($totalItemValue - $totalDiscValue);
            $mr->expense_amount = $totalExpValue;
            $totalAmount = (($totalItemValue - $totalDiscValue) + $totalExpValue);
            $mr->total_amount = $totalAmount;
            $mr->save();

            /*Create document submit log*/
            if ($request->document_status == ConstantHelper::SUBMITTED) {
                $bookId = $mr->book_id;
                $docId = $mr->id;
                $remarks = $mr->remark;
                $attachments = $request->file('attachment');
                $currentLevel = $mr->approval_level;
                $revisionNumber = $mr->revision_number ?? 1;
                $actionType = 'submit'; // Approve // reject // submit
                $approveDocument = Helper::approveDocument($bookId, $docId, $revisionNumber , $remarks, $attachments, $currentLevel, $actionType);
            }
            DB::commit();

            return response()->json([
                'message' => 'Record created successfully',
                'data' => $mr,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $user = Helper::getAuthenticatedUser();

        $mr = MrHeader::with(['items', 'book'])
            ->findOrFail($id);
        $bookTypeAlias = ConstantHelper::MR_SERVICE_ALIAS;
        $books = Helper::getBookSeries($bookTypeAlias)->get();
        $totalItemValue = $mr->items()->sum('basic_value');
        // $vendors = Vendor::where('status', ConstantHelper::ACTIVE)->get();
        // $paymentTerms = PaymentTerm::where('status', ConstantHelper::ACTIVE)->get();
        // $currencies = Currency::where('status', ConstantHelper::ACTIVE)->get();
        // $countries = Country::where('status', ConstantHelper::ACTIVE)->get();
        $items = Item::where('status', ConstantHelper::ACTIVE)->get();
        $hsns = Hsn::where('status', ConstantHelper::ACTIVE)->get();
        $units = Unit::where('status', ConstantHelper::ACTIVE)->get();
        $discountTypes = ConstantHelper::DISCOUNT_TYPES;
        // $purchaseOrders = PurchaseOrder::get();
        $revision_number = $mr->revision_number;
        $userType = Helper::userCheck();
        $buttons = Helper::actionButtonDisplay($mr->book_id,$mr->document_status , $mr->id, $mr->total_amount, $mr->approval_level, $mr->created_by ?? 0, $userType['type'], $revision_number);
        // $approvalHistory = Helper::getApprovalHistory($mr->series_id, $mr->id, $mr->revision_number);
        $docStatusClass = ConstantHelper::DOCUMENT_STATUS_CSS[$mr->document_status];
        // $revisionNumbers = $approvalHistory->pluck('revision_number')->unique()->values()->all();
        // $erpStores = ErpStore::where('organization_id', $user->organization_id)
        //     ->orderBy('id', 'DESC')
        //     ->get();

        return view('procurement.material-request.edit', [
            'mr' => $mr,
            'hsns' => $hsns,
            'books'=>$books,
            'units' => $units,
            'items' => $items,
            // 'vendors' => $vendors,
            // 'erpStores'=>$erpStores,
            // 'countries' => $countries,
            // 'currencies' => $currencies,
            // 'paymentTerms' => $paymentTerms,
            // 'discountTypes'=>$discountTypes,
            // 'purchaseOrders'=> $purchaseOrders,
            'buttons' => $buttons,
            'totalItemValue' => $totalItemValue,
            // 'approvalHistory' => $approvalHistory,
            'docStatusClass' => $docStatusClass,
            'revision_number' => $revision_number,
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            # Mr Header save
            $user = Auth::guard('web')->user();
            $organization = Organization::where('id', $user->organization_id)->first();
            $mr = MrHeader::find($id);
            $mr->fill($request->all());
            $mr->organization_id = $organization->id;
            $mr->group_id = $organization->group_id;
            $mr->company_id = $organization->company_id;
            $mr->document_date = date('Y-m-d', strtotime($request->mr_date));
            // $mrn->gate_entry_date = date('Y-m-d', strtotime($request->gate_entry_date));
            // $mrn->billing_address = $request->billing_address_detail;
            // $mrn->shipping_address = $request->shipping_address_detail;
            $mr->final_remarks = $request->remarks;
            $mr->save();

            foreach($request->components as $component) {
                # Mr Detail Save
                $mrDetail = MrnDetail::find($component['id']);
                $mrDetail->mr_header_id = $mr->id;
                $mrDetail->item_id = $component['item_id'];
                $mrDetail->item_code = $component['item_code'];
                $mrDetail->order_qty = $component['order_qty'] ?? 0.00;
                $mrDetail->receipt_qty = $component['receipt_qty'] ?? 0.00;
                $mrDetail->accepted_qty = $component['accepted_qty'] ?? 0.00;
                $mrDetail->rejected_qty = $component['rejected_qty'] ?? 0.00;
                $mrDetail->rate = $component['rate'] ?? 0.00;
                $mrDetail->basic_value = $component['basic_value'] ?? 0.00;
                $mrDetail->discount_amount = $component['discount_amount'] ?? 0.00;
                $mrDetail->net_value = $component['net_value'] ?? 0.00;
                $mrDetail->uom_id = $component['uom_id'];
                // $mrDetail->remark = $component['remark'];
                $mrDetail->save();

                #Save item discounts
                if (isset($component['discount_data']) && $component['discount_data']) {
                    $discountData =  json_decode($component['discount_data'], true);
                    foreach($discountData as $val) {
                        $extraAmount = MrTed::where('ted_level', '=', 'Item')
                            ->where('mrn_detail_id', '=', $mrDetail->id)
                            ->first();
                        $extraAmount->mr_header_id = $mr->id;
                        $extraAmount->mr_detail_id = $mrDetail->id;
                        $extraAmount->ted_type = 'Discount';
                        $extraAmount->ted_level = 'Item';
                        $extraAmount->ted_code = $val['name'];
                        $extraAmount->ted_percentage = $val['percentage'];
                        $extraAmount->ted_amount = $val['value'];
                        $extraAmount->applicability_type = 'Deduction';
                        $extraAmount->save();
                    }
                }

            }

            #Save summary discount
            if (isset($request->header['discount_data']) && $request->header['discount_data'] && $mrn->id) {
                $discountData =  json_decode($request->header['discount_data'], true);
                foreach($discountData as $val) {
                    $mrHD = MrTed::where('ted_level', '=', 'Header')
                        ->where('mr_header_id', '=', $mr->id)
                        ->first();
                    $mrHD->mr_header_id = $mr->id;
                    $mrHD->ted_type = 'Discount';
                    $mrHD->ted_level = 'Header';
                    $mrHD->ted_code = $val['name'];
                    $mrHD->ted_percentage = $val['percentage'];
                    $mrHD->ted_amount = $val['value'];
                    $mrHD->applicability_type = 'Deduction';
                    $mrHD->save();
                }
            }

            #Save summary Expenses
            if (isset($request->expense['data']) && $request->expense['data'] && $mr->id) {
                $expenseData =  json_decode($request->expense['data'], true);
                foreach($expenseData as $val) {
                    $mrnHD = MrTed::where('ted_level', '=', 'Header')
                        ->where('mrn_header_id', '=', $mr->id)
                        ->first();
                    $mrnHD->mrn_header_id = $mr->id;
                    $mrnHD->ted_type = 'Expense';
                    $mrnHD->ted_level = 'Header';
                    $mrnHD->ted_code = $val['name'];
                    $mrnHD->ted_percentage = $val['percentage'];
                    $mrnHD->ted_amount = $val['value'];
                    $mrnHD->applicability_type = 'Deduction';
                    $mrnHD->save();
                }
            }

            $mr->sub_total = $request->sub_total;
            $mr->discount_amount = $request->header_discount_amount;
            $mr->other_expenses = $request->expense_amount;
            $mr->total_amount = $request->total_amount;
            $mr->status = $request->form_status;
            $mr->save();

            # Save attachment file
            // if ($request->hasFile('attachment')) {
            //     $mrn->addMediaFromRequest('attachment')->toMediaCollection('attachment');
            // }

            DB::commit();

            return response()->json([
                'message' => 'Record updated successfully',
                'data' => $mr,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while creating the record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
