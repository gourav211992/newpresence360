@foreach ($pi->pi_items as $key => $pi_item)
    @php
        $rowCount = $key + 1;
    @endphp
    <tr id="row_{{ $rowCount }}" data-index="{{ $rowCount }}" @if ($rowCount < 2) class="trselected" @endif>
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input" id="Email_{{ $rowCount }}" value="{{ $rowCount }}" data-id="{{ $pi_item?->id }}">
                <label class="form-check-label" for="Email_{{ $rowCount }}"></label>
            </div>
        </td>
        <td class="poprod-decpt">
            <input type="text" @if ($pi_item->po_item) readonly @endif name="component_item_name[{{ $rowCount }}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " value="{{ $pi_item?->item_code }}" />
            <input type="hidden" name="components[{{ $rowCount }}][item_id]" value="{{ $pi_item?->item_id }}" />
            <input type="hidden" name="components[{{ $rowCount }}][item_code]" value="{{ $pi_item?->item_code }}" />
            <input type="hidden" name="components[{{ $rowCount }}][hsn_id]" value="{{ $pi_item?->hsn_id }}" />
            <input type="hidden" name="components[{{ $rowCount }}][hsn_code]" value="{{ $pi_item?->hsn_code }}" />
            @php
                $selectedAttr = $pi_item?->attributes ? $pi_item->attributes()->whereNotNull('attribute_value')->pluck('attribute_value')->all() : [];
            @endphp
            @foreach ($pi_item->attributes as $attributeHidden)
                <input type="hidden" name="components[{{ $rowCount }}][attr_group_id][{{ $attributeHidden?->attribute_name }}][attr_id]" value="{{ $attributeHidden?->id }}">
            @endforeach
            @foreach ($pi_item?->item?->itemAttributes ?? [] as $itemAttribute)
                @if (count($selectedAttr))
                    @foreach ($itemAttribute->attributes() as $value)
                        @if (in_array($value->id, $selectedAttr))
                            <input type="hidden" name="components[{{ $rowCount }}][attr_group_id][{{ $itemAttribute?->attribute_group_id }}][attr_name]" value="{{ $value?->id }}">
                        @endif
                    @endforeach
                @else
                    <input type="hidden" name="components[{{ $rowCount }}][attr_group_id][{{ $itemAttribute?->attribute_group_id }}][attr_name]" value="">
                @endif
            @endforeach
        </td>
        <td>
            <input type="text" name="components[{{ $rowCount }}][item_name]" class="form-control mw-100 mb-25" readonly value="{{ $pi_item?->item?->item_name }}" />
        </td>
        <td class="poprod-decpt attributeBtn" {{ $pi_item?->so_pi_mapping_item?->count() ? 'data-disabled="true" ' : '' }} id="itemAttribute_{{ $rowCount }}" data-count="{{ $rowCount }}" attribute-array="{{ $pi_item->item_attributes_array() }}">
        </td>
        <td>
            <input type="hidden" name="components[{{ $rowCount }}][inventoty_uom_id]" value="{{ $pi_item->inventoty_uom_id }}">
            <select {{ $pi_item?->so_pi_mapping_item?->count() ? 'disabled' : '' }} class="form-select mw-100 " name="components[{{ $rowCount }}][uom_id]">
                <option value="{{ $pi_item?->uom?->id }}" selected>{{ ucfirst($pi_item?->uom?->name) }}</option>
                @foreach ($pi_item?->item?->alternateUOMs ?? [] as $alternateUOM)
                    <option value="{{ $alternateUOM?->uom?->id }}">{{ $alternateUOM?->uom?->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" class="form-control mw-100 text-end" value="{{ $pi_item?->required_qty }}" name="components[{{ $rowCount }}][qty]" step="any">
        </td>
        <td>
            <input type="number" step="any" class="form-control mw-100 text-end disabled-input" name="components[{{ $rowCount }}][avl_stock]" value="{{ $pi_item->getAvlStockForPi() }}">
        </td>
        <td>
            <input type="number" step="any" class="form-control mw-100 text-end disabled-input" name="components[{{ $rowCount }}][pending_po]" value="{{ $pi_item?->pending_po }}">
        </td>
        <td>
            <input type="number" step="any" class="form-control mw-100 text-end" name="components[{{ $rowCount }}][adj_qty]" value="{{ $pi_item?->adjusted_qty }}">
        </td>
        <td>
            <input @readonly(true) type="number" step="any" class="form-control mw-100 text-end" name="components[{{ $rowCount }}][indent_qty]" value="{{ $pi_item?->indent_qty }}">
        </td>

        <td>
            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="components[{{ $rowCount }}][vendor_code]" value="{{ $pi_item?->vendor?->company_name }}" />
            <input type="hidden" name="components[{{ $rowCount }}][vendor_id]" value="{{ $pi_item?->vendor_id }}">
        </td>
        @if (isset($soTrackingRequired) && $soTrackingRequired)
            <td>
                <input readonly type="text" name="components[{{ $rowCount }}][so_no]" class="form-control mw-100 mb-25" value="{{ $pi_item?->so ? $pi_item?->so->full_document_number : '' }}" />
            </td>
        @endif
        <td>
            <input type="text" name="components[{{ $rowCount }}][remark]" value="{{ $pi_item?->remarks }}" class="form-control mw-100 mb-25" />
        </td>
        <input type="hidden" name="components[{{ $rowCount }}][pi_item_id]" value="{{ $pi_item?->id }}">
        <input type="hidden" name="components[{{ $rowCount }}][po_item_id]" value="{{ $pi_item?->po_item?->id }}">
        <input type="hidden" name="components[{{ $rowCount }}][so_id]" value="{{ $pi_item?->so_id }}">
    </tr>
@endforeach
