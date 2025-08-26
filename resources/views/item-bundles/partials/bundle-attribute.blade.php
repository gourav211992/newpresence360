@foreach($itemAttributes as $attribute)
@php
    $attrGroupName = $attribute?->attributeGroup?->name ?? 'Attribute';
    $selectedValueId = null;

    // Check for selected value from previous selection or old attributes
    foreach ($attribute->attributes() as $value) {
        if(in_array($value->id, $selectedAttr)) {
            $selectedValueId = $value->id;
            break;
        }
        if (!$selectedValueId && isset($oldAttributes[$attribute->id])) {
            $selectedValueId = $oldAttributes[$attribute->id];
        }
    }
@endphp
<tr data-group-id="{{ $attribute->attribute_group_id }}">
    <td>{{ $attrGroupName }}</td>
    <td>
        <select class="form-select mw-100 attribute_select"
                name="bundle_detail_attributes[{{ $rowCount }}][attribute_value]"
                data-row-index="{{ $rowCount }}"
                data-attribute-id="{{ $attribute->id }}">
            <option value="">Select {{ $attrGroupName }}</option>
            @foreach ($attribute->attributes() as $value)
                <option value="{{ $value->id }}" {{ $selectedValueId == $value->id ? 'selected' : '' }}>
                    {{ $value->value }}
                </option>
            @endforeach
        </select>
    </td>
</tr>
@endforeach
