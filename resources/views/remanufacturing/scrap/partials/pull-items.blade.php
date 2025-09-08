<tr id="row_{{ $rowCount }}" data-mode="view" data-request-header="{{ $type }}_components" data-index="{{ $rowCount }}" @if ($rowCount < 2) class="trselected" @endif>

    <input type="hidden" name="pull_item_ids[]" value="{{ $item->id }}">
    <input type="hidden" name="{{ $type }}_components[{{ $rowCount }}][id]" value="{{ $item->id ?? '' }}">
    <input type="hidden" name="{{ $type }}_components[{{ $rowCount }}][item_id]" value="{{ $item->item_id ?? '' }}">
    <input type="hidden" name="{{ $type }}_components[{{ $rowCount }}][item_code]" value="{{ $item->item_code ?? '' }}">

    <td class="customernewsection-form">
        <div class="form-check form-check-primary custom-checkbox">
            <input type="checkbox" class="form-check-input" id="Email_{{ $rowCount }}" value="{{ $rowCount }}" data-id="{{ $item->id }}">
            <label class="form-check-label" for="Email_{{ $rowCount }}"></label>
        </div>
    </td>

    <td class="poprod-decpt">
        <input type="date" readonly class="form-control mw-100 mb-25 ledgerselecct" value="{{ $item->{$type}->document_date }}" />
    </td>

    <td class="poprod-decpt">
        <input type="text" readonly class="form-control mw-100 mb-25 ledgerselecct" value="{{ $item->{$type}->document_number }}" />
    </td>

    <td class="poprod-decpt">
        <input type="text" readonly class="form-control mw-100 mb-25 ledgerselecct comp_item_code" value="{{ $item->item_code }}" />
    </td>

    <td>
        <input type="text" readonly class="form-control mw-100 mb-25" value="{{ $item?->item?->item_name }}" />
    </td>

    @php
        $itemAttrArray = $item?->item_attributes_array() ?? [];
        $selectedAttr = $item?->attributes()?->pluck('attribute_value')->filter()->all() ?? [];
    @endphp

    @if ($selectedAttr)
        @foreach ($item?->item?->itemAttributes ?? [] as $itemAttribute)
            <input type="hidden" name="{{ $type }}_components[{{ $rowCount }}][attr_group_id][{{ $itemAttribute->attribute_group_id }}][item_attr_id]" value="{{ $itemAttribute->id }}">
            @foreach ($itemAttribute->attributes() ?? [] as $value)
                @if (in_array($value->id, $selectedAttr))
                    <input type="hidden" name="{{ $type }}_components[{{ $rowCount }}][attr_group_id][{{ $itemAttribute->attribute_group_id }}][attr_name]" value="{{ $value->id }}">
                @endif
            @endforeach
        @endforeach
    @endif

    <td class="poprod-decpt attributeBtn" id="itemAttribute_{{ $rowCount }}" data-count="{{ $rowCount }}" attribute-array='@json($itemAttrArray)'>
        <button type="button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">
            Attributes
        </button>
    </td>

    <td>
        <input type="hidden" value="{{ $item->inventoty_uom_id }}">
        <select class="form-select mw-100" disabled>
            <option value="{{ $item->uom->id }}">{{ ucfirst($item->uom->name) }}</option>
            @if ($item?->item?->alternateUOMs)
                @foreach ($item?->item?->alternateUOMs as $alternateUOM)
                    <option value="{{ $alternateUOM?->uom?->id }}" {{ $alternateUOM?->uom?->id == $item->inventory_uom_id ? 'selected' : '' }}>
                        {{ $alternateUOM?->uom?->name }}
                    </option>
                @endforeach
            @endif
        </select>
    </td>

    <td>
        <input type="number" class="form-control mw-100 text-end" max="{{ $item->rejected_qty }}" value="{{ $item->rejected_qty }}" step="any" readonly>
    </td>

    <td>
        <input type="text" class="form-control mw-100" name="{{ $type }}_components[{{ $rowCount }}][remarks]" value="{{ $item->remarks }}">
    </td>
</tr>
