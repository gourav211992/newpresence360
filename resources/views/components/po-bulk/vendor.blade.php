<select class="form-select vendor-select" style="min-width: 150px" name="components[{{$rowCount}}][vendor_id]">
    @if ($defaultOption)
        <option value=""></option>
    @endif
    @foreach ($vendors as $vendor)
        <option value="{{ $vendor->id }}" {{ $vendor->id == $firstVendorId ? 'selected' : '' }}>
            {{ $vendor?->company_name ?? '' }}
        </option>
    @endforeach
</select>