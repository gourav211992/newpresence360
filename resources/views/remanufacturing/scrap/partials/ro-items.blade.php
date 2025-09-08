@foreach ($items as $key => $item)
    @php
        $rowCount = $key + 1;
    @endphp
    <tr id="row_{{ $rowCount }}" data-index="{{ $rowCount }}" @if ($rowCount < 2) class="trselected" @endif>
        <input type="hidden" name="pull_item_ids[]" value="{{ $item->id }}">
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input" id="Email_{{ $rowCount }}" value="{{ $rowCount }}" data-id="{{ $item->id }}">
                <label class="form-check-label" for="Email_{{ $rowCount }}"></label>
            </div>
        </td>
        <td class="poprod-decpt">
            <input type="text" readonly placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct" value="{{ $item->{$type}->document_date }}" />
        </td>
        <td class="poprod-decpt">
            <input type="text" readonly placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct" value="{{ $item->{$type}->document_number }}" />
        </td>
        <td class="poprod-decpt">
            <input type="text" readonly placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " value="{{ $item->item_code }}" />
        </td>
        <td>
            <input type="text" value="{{ $item?->item?->item_name }}" class="form-control mw-100 mb-25" readonly />
        </td>
        <td class="poprod-decpt">
            <button type="button" class="btn p-25 btn-sm btn-outline-secondary" data-row-count="{{ $rowCount }}" style="font-size: 10px">Attributes</button>
        </td>
        <td>
            <input type="hidden" value="{{ $item->inventoty_uom_id }}">
            <select class="form-select mw-100 ">
                <option value="{{ $item->uom->id }}">{{ ucfirst($item->uom->name) }}</option>
                @if ($item?->item?->alternateUOMs)
                    @foreach ($item?->item?->alternateUOMs as $alternateUOM)
                        <option value="{{ $alternateUOM?->uom?->id }}" {{ $alternateUOM?->uom?->id == $item->inventory_uom_id ? 'selected' : '' }}>
                            {{ $alternateUOM?->uom?->name }}</option>
                    @endforeach
                @endif
            </select>
        </td>
        <td><input type="number" class="form-control mw-100 text-end" maxAmount="{{ $item->rejected_qty }}" value="{{ $item->rejected_qty }}" step="any">
        </td>
        <td><input type="text" class="form-control mw-100 text-end" value="{{ $item->remark }}" step="any">
        </td>
    </tr>
@endforeach
