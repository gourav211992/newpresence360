<select name="{{ $name }}" {!! $attributes ?? '' !!}>
    @foreach($options ?? [] as $key => $label)
        <option value="{{ $key }}" {{ isset($value) && $value == $key ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>