<div class="table-responsive pomrnheadtffotsticky">
    <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
        <thead>
            <tr>
                <th width="30px" class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input" id="select_all_items_checkbox" oninput="checkOrRecheckAllItems(this);">
                        <label class="form-check-label" for="select_all_items_checkbox"></label>
                    </div>
                </th>
                <th width="150px">Item Code</th>
                <th width="240px">Item Name</th>
                <th>Attributes</th>
                <th width="50px">UOM</th>
                <th width="100px" class="numeric-alignment">Requested Qty</th>
                <th class = "numeric-alignment">Rate</th>
                <th class = "numeric-alignment">Value</th> 
                <th class = "numeric-alignment">Discount</th>
                <th class = "numeric-alignment" width = "150px">Total</th> 
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody class="mrntableselectexcel" id="item_header">
            @if (isset($items) && count($items) > 0)
                @php
                    $docType = $order->document_type;
                @endphp
                @foreach ($items as $orderItemIndex => $orderItem)
                    <tr id="item_row_{{$orderItemIndex}}" class="item_header_rows" onclick="onItemClick('{{$orderItemIndex}}');" data-detail-id="{{$orderItem->id}}" data-id="{{$orderItem->id}}">
                        <input type="hidden" id="pq_item_id_{{$orderItemIndex}}" name="pq_item_id[]" value="{{$orderItem->id}}" {{$orderItem->is_editable ? '' : 'readonly'}}>
                        <td class="customernewsection-form">
                            <div class="form-check form-check-primary custom-checkbox">
                                <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_{{$orderItemIndex}}" del-index="{{$orderItemIndex}}">
                                <label class="form-check-label" for="item_checkbox_{{$orderItemIndex}}"></label>
                            </div>
                        </td>
                        <td class="poprod-decpt">
                            <input type="text" id="items_dropdown_{{$orderItemIndex}}" name="item_code[]" placeholder="Select" class="form-control mw-100 except_draft ledgerselecct comp_item_code ui-autocomplete-input {{$orderItem->is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$orderItem->item?->item_name}}" data-code="{{$orderItem->item?->item_code}}" data-id="{{$orderItem->item?->id}}" hsn_code="{{$orderItem->item?->hsn?->code}}" item-name="{{$orderItem->item?->item_name}}" specs="{{$orderItem->item?->specifications}}" attribute-array="{{$orderItem->item_attributes_array()}}" value="{{$orderItem->item?->item_code}}" {{$orderItem->is_editable ? '' : 'readonly'}} item-location="[]">
                            <input type="hidden" name="item_id[]" id="items_dropdown_{{$orderItemIndex}}_value" value="{{$orderItem->item_id}}">
                            @if ($orderItem->rfq_item_id)
                                <input type="hidden" name="rfq_item_ids[]" id="rfq_id_{{$orderItemIndex}}" value="{{$orderItem->rfq_item_ids}}">
                            @endif
                            @if ($orderItem->pwo_item_id)
                                <input type="hidden" name="pwo_item_id[]" id="pwo_id_{{$orderItemIndex}}" value="{{$orderItem->pwo_item_id}}">
                            @endif
                        </td>
                        <td class="poprod-decpt">
                            <input type="text" id="items_name_{{$orderItemIndex}}" class="form-control mw-100" value="{{$orderItem->item?->item_name}}" name="item_name[]" readonly>
                        </td>
                        <td class="poprod-decpt" id="attribute_section_{{$orderItemIndex}}">
                            <button id="attribute_button_{{$orderItemIndex}}" {{count($orderItem->item_attributes_array()) > 0 ? '' : 'disabled'}} type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_{{$orderItemIndex}}', '{{$orderItemIndex}}', {{ json_encode(!$orderItem->is_editable) }});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                            <input type="hidden" name="attribute_value_{{$orderItemIndex}}">
                        </td>
                        <td>
                            <select class="form-select" name="uom_id[]" id="uom_dropdown_{{$orderItemIndex}}">
                            </select>
                        </td>
                        <td><input type="text" id = "item_req_qty_{{$orderItemIndex}}" data-index = '{{$orderItemIndex}}' value='{{ $orderItem->request_qty ?? 0 }}' name = "item_req_qty[{{$orderItemIndex}}]" oninput = "changeItemQty(this, {{$orderItemIndex}});" onchange = "itemQtyChange(this, {{$orderItemIndex}})" class="form-control mw-100 text-end item_qty_input" onblur = "setFormattedNumericValue(this);"/></td>
                        <td><input type="text" id = "item_rate_{{$orderItemIndex}}" name = "item_rate[{{$orderItemIndex}}]" value='{{ $orderItem->rate ?? 0 }}' oninput = "changeItemRate(this, {{$orderItemIndex}});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td> 
                        <td><input type="text" id = "item_value_{{$orderItemIndex}}" disabled class="form-control mw-100 text-end item_values_input" value="{{ $orderItem->request_qty * $orderItem->rate }}" /></td>
                        <input type = "hidden" id = "header_discount_{{$orderItemIndex}}" value = "0" ></input>
                        <input type = "hidden" id = "header_expense_{{$orderItemIndex}}" ></input>
                        <td>
                            <div class="position-relative d-flex align-items-center">
                                <input type="text" id = "item_discount_{{$orderItemIndex}}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" />
                                <div class="ms-50">
                                    <button type = "button" onclick = "onDiscountClick('item_value_{{$orderItemIndex}}', {{$orderItemIndex}})" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                                </div>
                            </div>
                        </td>
                        <input type="hidden" id = "item_tax_{{$orderItemIndex}}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
                        <td><input type="text" id = "value_after_discount_{{$orderItemIndex}}"  disabled class="form-control mw-100 text-end item_val_after_discounts_input" /></td>
                        <td style = "{{request() -> type === 'so' ? '' : 'display:none;'}}"><input type="date" name = "delivery_date[{{$orderItemIndex}}]" id = "delivery_date_{{$orderItemIndex}}" class="form-control mw-100" value = "{{Carbon\Carbon::now() -> format('Y-m-d')}}" /></td>
                        <input type = "hidden" id = "value_after_header_discount_{{$orderItemIndex}}" class = "item_val_after_header_discounts_input" ></input>
                        <input type="hidden" id="item_total_{{$orderItemIndex}}" value="{{$orderItem->total_amount}}" name="item_total[]" class="form-control mw-100 text-end" >
                        <td>
                            <input type="text" id="item_remarks_{{$orderItemIndex}}" name="item_remarks[]" class="form-control mw-100 ledgerselecct {{$orderItem->is_editable ? '' : 'restrict'}}" value="{{ $orderItem->remarks ?? '' }}">
                        </td>
                    </tr>
                    <script>

                        </script>
                @endforeach
            @endif
            
        </tbody>
        
        <tfoot>
            <tr class="totalsubheadpodetail"> 
                <td colspan="5"></td>
                <td class="text-end" id = "all_items_total_qty">00.00</td>
                <td></td>
                <td class="text-end" id = "all_items_total_value">00.00</td>
                <td class="text-end" id = "all_items_total_discount">00.00</td>
                <input type = "hidden" id = "all_items_total_tax"></input>
                <td class="text-end all_tems_total_common" id = "all_items_total_total">00.00</td>
                <td></td>
            </tr>
            
            <tr valign="top">
                <td colspan="{{request() -> type === 'so' ? 8 : 7}}" rowspan="10">
                    <table class="table border">
                        <tr>
                            <td class="p-0">
                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                            </td>
                        </tr>
                        <tr> 
                            <td class="poprod-decpt">
                                <div id ="current_item_cat_hsn"></div>
                            </td> 
                        </tr>
                        <tr id = "current_item_specs_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_specs"></div>
                            </td> 
                        </tr> 
                        <tr id = "current_item_attribute_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_attributes"></div>
                            </td> 
                        </tr> 
                        <tr id = "current_item_stocks_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_stocks"></div>
                            </td> 
                        </tr> 
                        <tr> 
                            <td class="poprod-decpt">
                                <div id ="current_item_inventory_details"></div>
                            </td> 
                        </tr> 

                        <tr id = "current_item_delivery_schedule_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_delivery_schedule"></div>
                            </td> 
                        </tr> 

                        <tr id = "current_item_qt_no_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_qt_no"></div>
                            </td> 
                        </tr>

                        <tr id = "current_item_description_row">
                            <td class="poprod-decpt">
                                <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: <span style = "text-wrap:auto;" id = "current_item_description"></span></span>
                            </td>
                        </tr>
                    </table> 
                </td>
                <td colspan="4">
                    <table class="table border mrnsummarynewsty" id = "summary_table">
                        <tr>
                            <td colspan="2" class="p-0">
                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between"><strong>Order Summary</strong>
                                    <div class="addmendisexpbtn">
                                        <button type = "button" data-bs-toggle="modal" data-bs-target="#orderTaxes" class="btn p-25 btn-sm btn-outline-secondary" onclick = "onOrderTaxClick();">Taxes</button>
                                        <button type = "button" data-bs-toggle="modal" data-bs-target="#discountOrder" class="btn p-25 btn-sm btn-outline-secondary" onclick = "onOrderDiscountModalOpen();"><i data-feather="plus"></i> Discount</button>
                                        <button type = "button" data-bs-toggle="modal" data-bs-target="#expenses" class="btn p-25 btn-sm btn-outline-secondary" onclick = "onOrderExpenseModalOpen();"><i data-feather="plus"></i> Expenses</button>
                                    </div>                                   
                                </h6>
                            </td>
                        </tr>
                        <tr class="totalsubheadpodetail"> 
                            <td width="55%"><strong>Item Total</strong></td>  
                            <td class="text-end" id = "all_items_total_value_summary">00.00</td>
                        </tr>
                        <tr class=""> 
                            <td width="55%">Item Discount</td>  
                            <td class="text-end" id = "all_items_total_discount_summary">00.00</td>
                        </tr>
                        <tr class="totalsubheadpodetail"> 
                            <td width="55%"><strong>Taxable Value</strong></td>  
                            <td class="text-end" id = "all_items_total_total_summary">00.00</td>
                        </tr>
                        <tr class=""> 
                            <td width="55%">Taxes</td>  
                            <td class="text-end" id = "all_items_total_tax_summary">00.00</td>
                        </tr>
                        <tr class="totalsubheadpodetail"> 
                            <td width="55%"><strong>Total After Tax</strong></td>  
                            <td class="text-end" id = "all_items_total_after_tax_summary">00.00</td>
                        </tr>
                        <tr class=""> 
                            <td width="55%">Expenses</td>  
                            <td class="text-end" id = "all_items_total_expenses_summary">00.00</td>
                        </tr>
                        <input type = "hidden" name = "sub_total" value = "0.00"></input>
                        <input type = "hidden" name = "discount" value = "0.00"></input>
                        <input type = "hidden" name = "discount_amount" value = "0.00"></input>
                        <input type = "hidden" name = "other_expenses" value = "0.00"></input>
                        <input type = "hidden" name = "total_amount" value = "0.00"></input>
                        <tr class="voucher-tab-foot">
                            <td class="text-primary"><strong>Grand Total</strong></td>  
                            <td>
                                <div class="quottotal-bg justify-content-end"> 
                                    <h5 id = "grand_total">00.00</h5>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr> 

        </tfoot>

</table>
</div>
