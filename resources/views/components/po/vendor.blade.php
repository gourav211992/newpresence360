<select class="form-select vendor-select" name="vend_name">
    @if ($defaultOption)
        <option value=""></option>
    @endif
    @foreach ($vendors as $vendor)
        <option value="{{ $vendor->id }}" {{ $vendor->id == $firstVendorId ? 'selected' : '' }}>
            {{ $vendor?->company_name ?? '' }}
        </option>
    @endforeach
</select>