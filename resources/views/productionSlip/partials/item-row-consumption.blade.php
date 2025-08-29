@use(App\Helpers\ConstantHelper)
@if (isset($slip))
    @php
        $docType = $slip -> document_type;
    @endphp
    @foreach ($slip->consumptions as $psBomConsIndex => $psBomCons)

        <tr id = "item_row_{{$psBomConsIndex}}" class = "item_header_rows" data-detail-id = "{{$psBomCons -> id}}" data-id = "{{ $psBomCons -> id }}" data-index="{{ $psBomConsIndex }}" data-altr-id="{{ $psBomCons->base_item_id }}">
            <input type = 'hidden' name = "cons[{{$psBomConsIndex}}][mo_bom_cons_id]" value = "{{$psBomCons -> mo_bom_mapping_id}}">
            <input type = 'hidden' name = "cons[{{$psBomConsIndex}}][pslip_bom_cons_id]" value = "{{$psBomCons -> id}}">
            @if($slip->document_status == ConstantHelper::DRAFT)
                <td class="consumption-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        @if($psBomCons->base_item_id)
                            <span class="badge rounded-pill badge-light-danger">A</span>
                        @endif
                        <input type="checkbox" class="form-check-input consumption_row_checks" id="item_co_row_check_{{$psBomConsIndex}}" data-con-id="{{$psBomCons ?-> item_id}}">
                    </div>
                </td>
            @endif
            <td class="poprod-decpt">
                <input type="text" id="so_doc_{{$psBomConsIndex}}" name="cons[{{$psBomConsIndex}}][so_doc]" class="form-control mw-100 disabled-input"  value="{{$psBomCons?->so?->document_number}}">
            </td>
            <td class="poprod-decpt">
                <input type="text" id = "items_dropdown_{{$psBomConsIndex}}" name="item_code[{{$psBomConsIndex}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input disabled-input {{$psBomCons -> is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$psBomCons -> item ?-> item_name}}" data-code="{{$psBomCons -> item ?-> item_code}}" data-id="{{$psBomCons -> item ?-> id}}" hsn_code = "{{$psBomCons -> item ?-> hsn ?-> code}}" item-name = "{{$psBomCons -> item ?-> item_name}}" specs = "{{$psBomCons -> item ?-> specifications}}" attribute-array = "{{$psBomCons -> item_attributes_array()}}"  value = "{{$psBomCons -> item ?-> item_code}}" item-location = "[]">
                <input type = "hidden" name = "cons[{{$psBomConsIndex}}][item_id]" id = "items_dropdown_{{$psBomConsIndex}}_value" value = "{{$psBomCons -> item_id}}"></input>
            </td>
            <td class="poprod-decpt">
                <input type="text" id = "items_name_{{$psBomConsIndex}}" class="form-control mw-100 disabled-input"   value = "{{$psBomCons -> item ?-> item_name}} {{$psBomCons->rm_type == 'sf' ? (' - '.$psBomCons?->station?->name) : ''}}" name = "cons[{{$psBomConsIndex}}][item_name]">
            </td>
            <td class="poprod-decpt">
                <input type="text" id = "items_type_{{$psBomConsIndex}}" class="form-control mw-100 disabled-input"   value = "{{strtoupper(($psBomCons?->rm_type == 'sf' ? 'wip' : $psBomCons?->rm_type) ?? 'rm')}}" name = "cons[{{$psBomConsIndex}}][item_type]">
            </td>
            <td class="poprod-decpt" id="attribute_section_{{$psBomConsIndex}}">
                <button id = "attribute_button_{{$psBomConsIndex}}" type = "button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                <input type = "hidden" name = "cons[{{$psBomConsIndex}}][attribute_value]" value='@json($psBomCons->attributes)'/>
            </td>
            <td>
                <select class="form-select disabled-input" name = "cons[{{$psBomConsIndex}}][uom_id]" id = "uom_dropdown_{{$psBomConsIndex}}">
                    <option value="{{$psBomCons?->uom_id}}">{{$psBomCons?->uom?->name}}</option>
                </select>
            </td>
            <td>
                <input type="text" id = "consumption_item_qty_{{$psBomConsIndex}}" data-bom-qty="{{$psBomCons->qty}}" data-mo-product-id="{{$psBomCons?->pslip_item?->mo_product_id}}" value = "{{$psBomCons->required_qty}}" name = "cons[{{$psBomConsIndex}}][item_qty]"  class="form-control mw-100 text-end disabled-input"/>
                <input type="hidden" id = "item_bom_qty_{{$psBomConsIndex}}" value = "{{$psBomCons->qty}}" name = "cons[{{$psBomConsIndex}}][item_bom_qty]"/>
            </td>
            <td>
                <input type="text" id = "consumption_qty_{{$psBomConsIndex}}" value = "{{$psBomCons->consumption_qty}}" name = "cons[{{$psBomConsIndex}}][consumption_qty]" class="form-control mw-100 text-end"/>
            </td>
            <td>
                @if(in_array($slip->document_status ?? [], ConstantHelper::DOCUMENT_STATUS_APPROVED))
                {{-- <input type="text" id = "item_avl_rate_{{$psBomConsIndex}}" value = "{{number_format($psBomCons->rate,4)}}" name = "cons[{{$psBomConsIndex}}][item_avl_rate]"  class="form-control mw-100 text-end" readonly/> --}}
                @else
                    <input type="text" id = "item_avl_qty_{{$psBomConsIndex}}" value = "{{$psBomCons->avl_stock}}" name = "cons[{{$psBomConsIndex}}][item_avl_qty]"  class="form-control mw-100 text-end" readonly/>
                @endif
                {{-- <input type="text" id = "item_qty_{{$psBomConsIndex}}" value = "{{$psBomCons -> qty}}" name = "item_qty[{{$psBomConsIndex}}]"  class="form-control mw-100 text-end disabled-input"/> --}}
            </td>
            {{-- @if(in_array($slip->document_status ?? [], ConstantHelper::DOCUMENT_STATUS_APPROVED))
            <td>
                <input type="text" id = "item_avl_value_{{$psBomConsIndex}}" value = "{{number_format($psBomCons->item_value,2)}}" name = "cons[{{$psBomConsIndex}}][item_avl_item_value]"  class="form-control mw-100 text-end" readonly/>
            </td>
            @endif --}}
            {{-- <input type="hidden" id="mo_product_id_{{$psBomConsIndex}}" name = "mo_product_id[{{$psBomConsIndex}}]"  value="{{$psBomCons?->mo_product_id}}">
            <input type="hidden" id="mo_id_{{$psBomConsIndex}}" name="mo_id[{{$psBomConsIndex}}]"  value="{{$psBomCons?->mo?->id}}">
            <input type="hidden" id="so_id_{{$psBomConsIndex}}" name="so_id[{{$psBomConsIndex}}]"  value="{{$psBomCons?->so_id}}">
            <input type="hidden" id="so_item_id_{{$psBomConsIndex}}" name="so_item_id[{{$psBomConsIndex}}]"  value="{{$psBomCons?->so_item_id}}">
            <input type="hidden" id="station_id_{{$psBomConsIndex}}" name = "station_id[{{$psBomConsIndex}}]"  value = "{{$psBomCons?->station_id}}"> --}}

            {{-- @if ($psBomCons->base_item_id!=''&&$psBomCons->base_item_id!=null)
                <input type="hidden" id="alternate_id_{{$psBomConsIndex}}" name="cons[{{$psBomConsIndex}}][alternate_id]"  value="{{$psBomCons->base_item_id}}">
            @endif --}}

        </tr>
    @endforeach
@endif
