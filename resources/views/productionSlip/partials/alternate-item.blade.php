

<tr id="item_row_{{$rowIndex}}" class="item_header_rows" data-detail-id="{{$mo_bom_cons_id}}" data-id="{{$mo_bom_cons_id}}" data-index="{{ $rowIndex }}" data-altr-id="{{ $mo_bom_cons_id }}">
    <td class="consumption-form">

        <div class="form-check form-check-primary custom-checkbox">
            <span class="badge rounded-pill badge-light-danger">A</span>
            <input type="checkbox" class="form-check-input consumption_row_checks" id="item_co_row_check_{{$rowIndex}}"
                data-con-id="{{$itemId}}">
        </div>
    </td>
    <input type="hidden" name="cons[{{$rowIndex}}][mo_bom_cons_id]" value="{{$mo_bom_cons_id}}">
    <td class="poprod-decpt">
        <input type="text" id="so_doc_{{$rowIndex}}" name="cons[{{$rowIndex}}][so_doc]" class="form-control mw-100" readonly=""
            value="{{$soDoc}}">
    </td>
    <td class="poprod-decpt">
        <input type="text" id = "items_dropdown_{{$rowIndex}}" name="item_code[{{$rowIndex}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" />
        <input type="hidden" name="cons[{{$rowIndex}}][item_id]" id="items_dropdown_{{$rowIndex}}_value" value="{{$itemId}}">
    </td>
    <td class="poprod-decpt">
        <input type="text" id="items_name_{{$rowIndex}}" class="form-control mw-100" readonly="" value=""
            name="cons[{{$rowIndex}}][item_name]">
    </td>
    <td class="poprod-decpt">
        <input type="text" id="items_type_{{$rowIndex}}" class="form-control mw-100" readonly="" value="{{$itemType}}"
            name="cons[{{$rowIndex}}][item_type]">
    </td>

    <td class="poprod-decpt attributeBtn" id="attribute_section_{{$rowIndex}}">
        <button id = "attribute_button_{{$rowIndex}}" type = "button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
    </td>
    <input type = "hidden" name = "cons[{{$rowIndex}}][attribute_value]"/>

    <td>
        <select class="form-select" readonly="" name="cons[{{$rowIndex}}][uom_id]" id="uom_dropdown_{{$rowIndex}}">

        </select>
    </td>
    <td>
        <input type="text" id="item_qty_{{$rowIndex}}" data-bom-qty="3{{$rowIndex}}" data-mo-product-id="93" value="{{$item_qty}}"
            name="cons[{{$rowIndex}}][item_qty]" class="form-control mw-100 text-end" readonly="">
        <input type="hidden" id="item_bom_qty_{{$rowIndex}}" value="{{$rowIndex}}" name="cons[{{$rowIndex}}][item_bom_qty]">
    </td>
    <td>
        <input type="text" id="consumption_qty_{{$rowIndex}}" data-bom-qty="30" data-mo-product-id="93" value=""
            name="cons[{{$rowIndex}}][consumption_qty]" class="form-control mw-100 text-end">

    </td>
    <td>
        <input type="text" id="item_avl_qty_{{$rowIndex}}" value="0" name="cons[{{$rowIndex}}][item_avl_qty]"
            class="form-control mw-100 text-end" readonly="">
    </td>

    <input type="hidden" id="alternate_id_{{$rowIndex}}" name="cons[{{$rowIndex}}][alternate_id]"  value="{{$mo_bom_cons_id}}">


</tr>
