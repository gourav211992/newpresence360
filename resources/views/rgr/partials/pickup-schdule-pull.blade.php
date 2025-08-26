@foreach($pickupItems as $index => $item)

<tr id="row_{{ $index }}" data-index="{{ $index }}" class="item_detail_row">
    <td class="customernewsection-form">
        <div class="form-check form-check-primary custom-checkbox">
            <input type="hidden" name="rgr_items[{{ $index }}][id]" value="{{ $item['item_id'] }}">
            <input type="checkbox" class="form-check-input analyze_row" id="Email_{{ $index }}" value="{{ $item['item_id'] }}">
            <label class="form-check-label" for="Email_{{ $index }}"></label>
        </div>
    </td>

    <td class="poprod-decpt">
        {{ $item['item_code'] ?? '' }}
        <input type="hidden" name="rgr_items[{{ $index }}][item_id]" value="{{ $item['item_id'] }}">
        <input type="hidden" name="rgr_items[{{ $index }}][item_code]" value="{{ $item['item_code'] }}">
        <input type="hidden" name="rgr_items[{{ $index }}][item_name]" value="{{ $item['item_name'] }}">
    </td>

    <td class="poprod-decpt">
        {{ $item['item_name'] ?? '' }}
    </td>

   <td class="poprod-decpt">
    @php
        $selectedAttrValues = collect($item['attribute'] ?? [])
            ->map(function ($group) {
                $selected = collect($group['values_data'])
                    ->first(fn($attr) => ($attr['selected'] ?? $attr?->selected) === true);

                if ($selected) {
                    return [
                        // Bahar wala "id"
                        'item_attribute_id'   => $group['id'],  

                        // Group Info
                        'attribute_group_id'  => $group['attribute_group_id'],
                        'attribute_group_name'=> $group['group_name'],

                        // Selected Value Info
                        'attribute_id'        => $selected->id,
                        'attribute_value'     => $selected->value,
                    ];
                }
                return null;
            })
            ->filter()
            ->values();
    @endphp

    {{-- 🔹 UI Display --}}
    @foreach ($selectedAttrValues as $selectedAttrValue)
        <span class="badge rounded-pill badge-light-primary">
            <strong>{{ $selectedAttrValue['attribute_group_name'] }}</strong>: 
            {{ $selectedAttrValue['attribute_value'] }}
        </span>
    @endforeach

    {{-- 🔹 Hidden Inputs for Store --}}
    @foreach ($selectedAttrValues as $attrIndex => $selectedAttrValue)
        <input type="hidden" 
               name="rgr_items[{{ $index }}][rgr_item_attributes][{{ $attrIndex }}][item_attribute_id]" 
               value="{{ $selectedAttrValue['item_attribute_id'] }}">

        <input type="hidden" 
               name="rgr_items[{{ $index }}][rgr_item_attributes][{{ $attrIndex }}][attr_name]" 
               value="{{ $selectedAttrValue['attribute_group_id'] }}">

        <input type="hidden" 
               name="rgr_items[{{ $index }}][rgr_item_attributes][{{ $attrIndex }}][attribute_name]" 
               value="{{ $selectedAttrValue['attribute_group_name'] }}">

         <input type="hidden" 
               name="rgr_items[{{ $index }}][rgr_item_attributes][{{ $attrIndex }}][attribute_value]" 
               value="{{ $selectedAttrValue['attribute_value'] }}">

        <input type="hidden" 
               name="rgr_items[{{ $index }}][rgr_item_attributes][{{ $attrIndex }}][attr_value]" 
               value="{{ $selectedAttrValue['attribute_id'] }}">

       
    @endforeach
</td>
</td>
    <td class="poprod-decpt">
        {{ $item['uom_name'] }}
        <input type="hidden" name="rgr_items[{{ $index }}][uom_id]" value="{{ $item['uom_id'] }}">
        <input type="hidden" name="rgr_items[{{ $index }}][uom_name]" value="{{ $item['uom_name'] }}">
    </td>

    <td class="poprod-decpt">
        {{ $item['qty'] ?? 0 }}
        <input type="hidden" name="rgr_items[{{ $index }}][qty]" value="{{ $item['qty'] ?? 0 }}">
    </td>

    <td class="poprod-decpt">
       {{ $item['uid'] }}
        <input type="hidden" name="rgr_items[{{ $index }}][item_uid]" value="{{ $item['uid'] ?? 0 }}">
    </td>

    <td class="poprod-decpt">
        {{ $item['customer_name'] ?? '' }}
        <input type="hidden" name="rgr_items[{{ $index }}][customer_id]" value="{{ $item['customer_id'] }}">
        <input type="hidden" name="rgr_items[{{ $index }}][customer_name]" value="{{ $item['customer_name'] ?? '' }}">
    </td>

    <!-- Hidden fields -->
    <input type="hidden" name="rgr_items[{{ $index }}][sub_store_id]" value="{{ $item['sub_store_id'] }}">
    <input type="hidden" name="rgr_items[{{ $index }}][category_id]" value="{{ $item['category_id'] }}">
    <input type="hidden" name="rgr_items[{{ $index }}][hsn_id]" value="{{ $item['hsn_id'] }}">
    <input type="hidden" name="rgr_items[{{ $index }}][hsn_code]" value="{{ $item['hsn_code'] ?? '' }}">
    <input type="hidden" name="rgr_items[{{ $index }}][item_remark]" value="{{ $item['item_remark'] ?? '' }}">
</tr>
@endforeach
