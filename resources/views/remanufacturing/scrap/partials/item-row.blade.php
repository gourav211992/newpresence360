<tr id="scavengingItemsTr_{{ $rowCount }}" data-index="{{ $rowCount }}">
    {{-- Checkbox --}}
    <td class="customernewsection-form">
        <div class="form-check form-check-primary custom-checkbox">
            <input type="checkbox" class="form-check-input" id="Email_{{ $rowCount }}" value="{{ $rowCount }}" data-id="{{ $item->id ?? '' }}" {{ $createEditDisabled }}>
            <label class="form-check-label" for="Email_{{ $rowCount }}"></label>
        </div>
    </td>

    {{-- Item Code Selector --}}
    <td class="poprod-decpt">
        <input type="text" name="component_item_name[{{ $rowCount }}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code" value="{{ old("component_item_name.$rowCount", $item->item_code ?? '') }}" {{ $createEditReadonly }} />
        <input type="hidden" name="components[{{ $rowCount }}][scrap_item_id]" value="{{ $item->id ?? '' }}">
        <input type="hidden" name="components[{{ $rowCount }}][item_id]" value="{{ $item->item_id ?? '' }}">
        <input type="hidden" name="components[{{ $rowCount }}][item_code]" value="{{ $item->item_code ?? '' }}">
        <input type="hidden" name="components[{{ $rowCount }}][hsn_id]" value="{{ $item->hsn_id ?? '' }}">
        <input type="hidden" name="components[{{ $rowCount }}][hsn_code]" value="{{ $item->hsn_code ?? '' }}">
        @php
            $itemAttrArray = isset($item) ? $item?->item_attributes_array() ?? [] : [];
            $selectedAttr = isset($item) ? $item?->attributes()?->pluck('attribute_value')->filter()->all() ?? [] : [];
        @endphp

        @if ($selectedAttr)
            @foreach ($item?->item?->itemAttributes ?? [] as $itemAttribute)
                <input type="hidden" name="components[{{ $rowCount }}][attr_group_id][{{ $itemAttribute->attribute_group_id }}][item_attr_id]" value="{{ $itemAttribute->id }}">
                @foreach ($itemAttribute->attributes() ?? [] as $value)
                    @if (in_array($value->id, $selectedAttr))
                        <input type="hidden" name="components[{{ $rowCount }}][attr_group_id][{{ $itemAttribute->attribute_group_id }}][attr_name]" value="{{ $value->id }}">
                    @endif
                @endforeach
            @endforeach
        @endif
    </td>

    {{-- Item Name --}}
    <td>
        <input type="text" name="components[{{ $rowCount }}][item_name]" class="form-control mw-100 mb-25" value="{{ $item->item_name ?? '' }}" readonly />
    </td>

    {{-- Attributes --}}
    <td class="poprod-decpt attributeBtn" id="itemAttribute_{{ $rowCount }}" data-count="{{ $rowCount }}" attribute-array='@json($itemAttrArray)'>
        <button type="button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px" {{ $createEditDisabled }}>
            Attributes
        </button>
    </td>

    {{-- UOM Dropdown --}}
    <td>
        <input type="hidden" name="components[{{ $rowCount }}][inventoty_uom_id]" value="{{ $item->inventoty_uom_id ?? '' }}">

        <select class="form-select mw-100" name="components[{{ $rowCount }}][uom_id]" {{ $createEditReadonly }}>
            @if (isset($item) && $item?->uom)
                <option value="{{ $item->uom->id }}">{{ ucfirst($item->uom->name) }}</option>
            @else
                <option value="">Select UOM</option>
            @endif

            @foreach ($item?->item?->alternateUOMs ?? [] as $alternateUOM)
                @php $uomId = $alternateUOM?->uom?->id; @endphp
                @if ($uomId)
                    <option value="{{ $uomId }}" {{ $uomId == ($item->inventory_uom_id ?? null) ? 'selected' : '' }}>
                        {{ $alternateUOM?->uom?->name }}
                    </option>
                @endif
            @endforeach
        </select>
    </td>

    {{-- Qty --}}
    <td>
        <input type="number" {{ $createEditReadonly }} step="any" class="form-control text-end mw-100" name="components[{{ $rowCount }}][qty]" value="{{ $item->qty ?? '' }}">
    </td>

    {{-- Rate --}}
    <td>
        <input type="number" step="any" class="form-control text-end mw-100" name="components[{{ $rowCount }}][rate]" value="{{ $item->rate ?? '' }}" readonly>
    </td>

    {{-- Total Cost --}}
    <td>
        <input type="number" {{ $createEditReadonly }} step="any" class="form-control text-end mw-100" name="components[{{ $rowCount }}][total_cost]" value="{{ $item->total_cost ?? '' }}">
    </td>

    {{-- Cost Center --}}
    <td>
        <input type="text" {{ $createEditReadonly }} name="components[{{ $rowCount }}][cost_center]" placeholder="Select Cost Center" class="form-control mw-100 ledgerselecct ui-autocomplete-input comp_item_code_cost_centers" value="{{ $item->cost_center ?? '' }}">
        <input type="hidden" name="components[{{ $rowCount }}][cost_center_id]" value="{{ $item->cost_center_id ?? '' }}">
    </td>

    {{-- Remark --}}
    <td>
        <input type="text" {{ $createEditReadonly }} class="form-control mw-100" name="components[{{ $rowCount }}][remark]" value="{{ $item->remarks ?? '' }}">
    </td>
</tr>
