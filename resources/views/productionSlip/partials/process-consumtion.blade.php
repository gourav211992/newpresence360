@foreach ($consumptions as $pwoBomConsIndex => $consumption)

    <tr id = "item_row_{{$pwoBomConsIndex}}" class = "item_header_rows" data-detail-id = "{{$consumption ?-> id}}" data-id = "{{$consumption ?-> id}}" data-lastindex="{{ count($consumptions) - 1 }}" data-index="{{ $pwoBomConsIndex }}">
        <td class="consumption-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input consumption_row_checks" id="item_co_row_check_{{$pwoBomConsIndex}}" data-con-id="{{$consumption ?-> item_id}}">
            </div>
        </td>
        <input type = 'hidden' name = "cons[{{$pwoBomConsIndex}}][mo_bom_cons_id]" value = "{{$consumption ?-> id}}">
        <td class="poprod-decpt">
            <input type="text" id="so_doc_{{$pwoBomConsIndex}}" name="cons[{{$pwoBomConsIndex}}][so_doc]" class="form-control mw-100" readonly  value="{{strtoupper($consumption?->so?->book_code)}} - {{$consumption?->so?->document_number}}">
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "items_dropdown_{{$pwoBomConsIndex}}" name="item_code[{{$pwoBomConsIndex}}]" placeholder="Select" readonly class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input  readonlyrestrict" autocomplete="off" data-name="{{$consumption -> item ?-> item_name}}" data-code="{{$consumption -> item ?-> item_code}}" data-id="{{$consumption -> item ?-> id}}" hsn_code = "{{$consumption -> item ?-> hsn ?-> code}}" item-name = "{{$consumption -> item ?-> item_name}}" specs = "{{$consumption -> item ?-> specifications}}" attribute-array = "{{$consumption -> item_attributes_array()}}"  value = "{{$consumption -> item ?-> item_code}}" item-location = "[]">
            <input type = "hidden" name = "cons[{{$pwoBomConsIndex}}][item_id]" id = "items_dropdown_{{$pwoBomConsIndex}}_value" value = "{{$consumption -> item_id}}"></input>
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "items_name_{{$pwoBomConsIndex}}" class="form-control mw-100" readonly   value = "{{$consumption -> item ?-> item_name}} {{$consumption->rm_type == 'sf' ? (' - '.$consumption?->station?->name) : ''}}" name = "cons[{{$pwoBomConsIndex}}][item_name]">
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "items_type_{{$pwoBomConsIndex}}" class="form-control mw-100" readonly   value = "{{strtoupper(($consumption?->rm_type == 'sf' ? 'wip' : $consumption?->rm_type) ?? 'rm')}}" name = "cons[{{$pwoBomConsIndex}}][item_type]">
        </td>
        <td class="poprod-decpt" id="attribute_section_{{$pwoBomConsIndex}}">
            <button id = "attribute_button_{{$pwoBomConsIndex}}" {{count($consumption->item_attributes_array()) > 0 ? '' : 'disabled'}} type = "button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
            <input type = "hidden" name = "cons[{{$pwoBomConsIndex}}][attribute_value]" />
        </td>
        <td>
            <select class="form-select" readonly name = "cons[{{$pwoBomConsIndex}}][uom_id]" id = "uom_dropdown_{{$pwoBomConsIndex}}">
                <option value="{{$consumption?->uom_id}}">{{$consumption?->uom?->name}}</option>
            </select>
        </td>
        <td>
            <input type="text" id = "consumption_item_qty_{{$pwoBomConsIndex}}" data-bom-qty="{{$consumption->bom_qty}}" data-mo-product-id="{{$consumption->mo_product_id}}" value = "{{$consumption->required_qty}}" name = "cons[{{$pwoBomConsIndex}}][item_qty]"  class="form-control mw-100 text-end" readonly/>
            <input type="hidden" id = "item_bom_qty_{{$pwoBomConsIndex}}" value = "{{$consumption->bom_qty}}" name = "cons[{{$pwoBomConsIndex}}][item_bom_qty]"/>
        </td>
        <td>
            <input type="text" id = "consumption_qty_{{$pwoBomConsIndex}}" data-bom-qty="{{$consumption->bom_qty}}" data-mo-product-id="{{$consumption->mo_product_id}}" value = "{{$consumption->required_qty}}" name = "cons[{{$pwoBomConsIndex}}][consumption_qty]" class="form-control mw-100 text-end"/>
            {{-- <input type="hidden" id = "item_bom_qty_{{$pwoBomConsIndex}}" value = "{{$consumption->bom_qty}}" name = "cons[{{$pwoBomConsIndex}}][item_bom_consumption_qty]"/> --}}
        </td>
        <td>
            <input type="text" id = "item_avl_qty_{{$pwoBomConsIndex}}" value = "{{$consumption->avl_stock}}" name = "cons[{{$pwoBomConsIndex}}][item_avl_qty]"  class="form-control mw-100 text-end" readonly/>
        </td>
        {{-- <input type="hidden" id="mo_product_id_{{$pwoBomConsIndex}}" name = "mo_product_id[{{$pwoBomConsIndex}}]"  value="{{$consumption?->mo_product_id}}">
        <input type="hidden" id="mo_id_{{$pwoBomConsIndex}}" name="mo_id[{{$pwoBomConsIndex}}]"  value="{{$consumption?->mo?->id}}">
        <input type="hidden" id="so_id_{{$pwoBomConsIndex}}" name="so_id[{{$pwoBomConsIndex}}]"  value="{{$consumption?->so_id}}">
        <input type="hidden" id="station_id_{{$pwoBomConsIndex}}" name = "station_id[{{$pwoBomConsIndex}}]"  value = "{{$consumption?->station_id}}"> --}}
        <input type="hidden" id="so_item_id_{{$pwoBomConsIndex}}" name="cons[{{$pwoBomConsIndex}}][so_item_id]"  value="{{$consumption?->so_item_id}}">
    </tr>
@endforeach
