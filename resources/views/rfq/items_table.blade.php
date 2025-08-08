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
                <th width="100px" class="numeric-alignment">Req. Quantity</th>
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
                        <input type="hidden" id="rfq_item_id_{{$orderItemIndex}}" name="rfq_item_id" value="{{$orderItem->id}}" {{$orderItem->is_editable ? '' : 'readonly'}}>
                        <td class="customernewsection-form">
                            <div class="form-check form-check-primary custom-checkbox">
                                <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_{{$orderItemIndex}}" del-index="{{$orderItemIndex}}">
                                <label class="form-check-label" for="item_checkbox_{{$orderItemIndex}}"></label>
                            </div>
                        </td>
                        <td class="poprod-decpt">
                            <input type="text" id="items_dropdown_{{$orderItemIndex}}" name="item_code[]" placeholder="Select" class="form-control mw-100 except_draft ledgerselecct comp_item_code ui-autocomplete-input {{$orderItem->is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$orderItem->item?->item_name}}" data-code="{{$orderItem->item?->item_code}}" data-id="{{$orderItem->item?->id}}" hsn_code="{{$orderItem->item?->hsn?->code}}" item-name="{{$orderItem->item?->item_name}}" specs="{{$orderItem->item?->specifications}}" attribute-array="{{$orderItem->item_attributes_array()}}" value="{{$orderItem->item?->item_code}}" {{$orderItem->is_editable ? '' : 'readonly'}} item-location="[]">
                            <input type="hidden" name="item_id[]" id="items_dropdown_{{$orderItemIndex}}_value" value="{{$orderItem->item_id}}">
                            @if ($orderItem->pi_item_id)
                                <input type="hidden" name="pi_item_ids[]" id="pi_id_{{$orderItemIndex}}" value="{{$orderItem->pi_item_ids}}">
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
                        <td class="numeric-alignment">
                            <input type="text" id="item_req_qty_{{$orderItemIndex}}" value="{{$orderItem->request_qty}}" name="item_req_qty[]" oninput='setVariance(this,{{$orderItemIndex}});setValue({{$orderItemIndex}});' class="form-control mw-100 text-end" >
                        </td>
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
                <td colspan="12"></td>
            </tr>  
            <tr valign="top">
                <td id = "item_details_td" colspan="12" rowspan="10">
                    <table class="table border">
                        <tr>
                            <td class="p-0">
                            <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between align-items-center">
                                <strong>Item Details</strong>
                                @if(isset($items) && count($items) > 0)
                                <span class="pagination laravel-paginate pagination-sm mb-0">
                                    <div id="pagination-wrapper btn-sm">
                                    
                                    {{ $items->onEachSide(1)->links('pagination::bootstrap-4') }}
                                    </div>
                                </span>
                                @endif
                            </h6>


                            </td>
                        </tr>   
                        <tr> 
                            <td class="poprod-decpt">
                                <div id ="current_item_cat_hsn">

                                </div>
                            </td> 
                        </tr>
                        <tr id = "current_item_specs_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_specs">

                                </div>
                            </td> 
                        </tr> 
                        <tr id = "current_item_attribute_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_attributes">

                                </div>
                            </td> 
                        </tr> 
                        
                        
                        <tr id = "current_item_qt_no_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_qt_no">

                                </div>
                            </td> 
                        </tr>

                        <tr id = "current_item_description_row">
                            <td class="poprod-decpt">
                                <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: <span style = "text-wrap:auto;" id = "current_item_description"></span></span>
                            </td>
                        </tr>
                    </table> 
                </td>
            </tr>
        </tfoot>
</table>
</div>
