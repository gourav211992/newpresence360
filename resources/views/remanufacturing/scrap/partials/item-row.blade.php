{{-- Create / Edit Row for Scavenging Items --}}
<tr id="scavengingItemsTr_{{ $rowCount }}" data-index="{{ $rowCount }}">
    {{-- Checkbox --}}
    <td class="customernewsection-form">
        <div class="form-check form-check-primary custom-checkbox">
            <input type="checkbox" class="form-check-input" id="Email_{{ $rowCount }}" value="{{ $rowCount }}"
                data-id="">
            <label class="form-check-label" for="Email_{{ $rowCount }}"></label>
        </div>
    </td>

    <td class="poprod-decpt">
        <input type="text" name="component_item_name[{{ $rowCount }}]" placeholder="Select"
            class="form-control mw-100 mb-25 ledgerselecct comp_item_code " />
        <input type="hidden" name="components[{{ $rowCount }}][item_id]" />
        <input type="hidden" name="components[{{ $rowCount }}][item_code]" />
        <input type="hidden" name="components[{{ $rowCount }}][hsn_id]" />
        <input type="hidden" name="components[{{ $rowCount }}][hsn_code]" />
    </td>

    <td>
        <input type="text" name="components[{{ $rowCount }}][item_name]" class="form-control mw-100 mb-25"
            readonly />
    </td>

    <td class="poprod-decpt attributeBtn" id="itemAttribute_{{ $rowCount }}" data-count="{{ $rowCount }}"
        attribute-array="">
        <button type="button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
    </td>
    <td>
        <select class="form-select mw-100" name="components[{{ $rowCount }}][uom_id]">
        </select>
    </td>
    <td>
        <input @readonly(true) type="number" step="any" class="form-control text-end mw-100"
            name="components[{{ $rowCount }}][qty]">
    </td>

    <td>
        <input type="text" name="components[{{ $rowCount }}][cost_center]" placeholder="Select Cost Center"
            class="form-control mw-100 ledgerselecct ui-autocomplete-input comp_item_code_cost_centers" />
        <input type="hidden" name="components[{{ $rowCount }}][cost_center_id]" />

    </td>
    <td>
        <input type="text" step="any" class="form-control mw-100 text-end"
            name="components[{{ $rowCount }}][remark]">
    </td>
</tr>
