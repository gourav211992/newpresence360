<tr id="scavengingItemsTr_{{ $rowCount }}" data-index="{{ $rowCount }}">
    {{-- Checkbox --}}
    <td class="customernewsection-form">
        <div class="form-check form-check-primary custom-checkbox">
            <input type="checkbox" class="form-check-input" id="Email_{{ $rowCount }}" value="{{ $rowCount }}" data-id="{{ $item->id ?? '' }}">
            <label class="form-check-label" for="Email_{{ $rowCount }}"></label>
        </div>
    </td>

    {{-- Item Code Selector --}}
    <td class="poprod-decpt">
        <input type="text" name="component_item_name[{{ $rowCount }}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code" value="{{ old("component_item_name.$rowCount", $item->item_code ?? '') }}" />
        <input type="hidden" name="components[{{ $rowCount }}][item_id]" value="{{ $item->item_id ?? '' }}">
        <input type="hidden" name="components[{{ $rowCount }}][item_code]" value="{{ $item->item_code ?? '' }}">
        <input type="hidden" name="components[{{ $rowCount }}][hsn_id]" value="{{ $item->hsn_id ?? '' }}">
        <input type="hidden" name="components[{{ $rowCount }}][hsn_code]" value="{{ $item->hsn_code ?? '' }}">
    </td>

    @php
        $itemAttrArray = isset($item) ? $item?->item_attributes_array() : [];
        $selectedAttr = isset($item) ? $item?->attributes()->pluck('attribute_value')->filter()->all() : [];
    @endphp

    {{-- Hidden inputs for already-selected attributes --}}
    @foreach ($selectedAttr as $attrValueId)
        <input type="hidden" name="components[{{ $rowCount }}][selected_attributes][]" value="{{ $attrValueId }}">
    @endforeach

    {{-- Item Name --}}
    <td>
        <input type="text" name="components[{{ $rowCount }}][item_name]" class="form-control mw-100 mb-25" value="{{ $item->item_name ?? '' }}" readonly />
    </td>

    {{-- Attributes Button --}}
    <td class="poprod-decpt attributeBtn" id="itemAttribute_{{ $rowCount }}" data-count="{{ $rowCount }}" data-attributes='@json($itemAttrArray)'>
        <button type="button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">
            Attributes
        </button>
    </td>

    {{-- UOM Dropdown --}}
    <td>
        <select class="form-select mw-100" name="components[{{ $rowCount }}][uom_id]">
            <option value="">Select</option>
            @foreach ($uoms ?? [] as $uomId => $uomName)
                <option value="{{ $uomId }}" {{ isset($item->uom_id) && $item->uom_id == $uomId ? 'selected' : '' }}>
                    {{ $uomName }}
                </option>
            @endforeach
        </select>
    </td>

    {{-- Qty --}}
    <td>
        <input type="number" step="any" class="form-control text-end mw-100" name="components[{{ $rowCount }}][qty]" value="{{ $item->qty ?? '' }}">
    </td>

    {{-- Cost Center --}}
    <td>
        <input type="text" name="components[{{ $rowCount }}][cost_center]" placeholder="Select Cost Center" class="form-control mw-100 ledgerselecct ui-autocomplete-input comp_item_code_cost_centers" value="{{ $item->cost_center ?? '' }}">
        <input type="hidden" name="components[{{ $rowCount }}][cost_center_id]" value="{{ $item->cost_center_id ?? '' }}">
    </td>

    {{-- Remark --}}
    <td>
        <input type="text" class="form-control mw-100 text-end" name="components[{{ $rowCount }}][remark]" value="{{ $item->remark ?? '' }}">
    </td>
</tr>
