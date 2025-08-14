<div class="p-2">
    <div class="row justify-content-between">
        <div class="col-md-3 mb-1 mb-sm-0">
            <form>
                <div class="d-flex align-items-center justify-content-center justify-content-sm-start">
                    <span>Show</span>
                    <select class="form-select mx-1" style="width: 70px" onchange="this.form.submit()" name="length">
                       @foreach ($pageLengths as $pageLength)
                            <option value="{{ $pageLength }}"
                                {{ Request::get('length') == $pageLength ? 'selected' : '' }}>{{ $pageLength }}
                            </option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </div>
                
                @foreach (Request::query() as $key => $value)
                    @if ($key != 'length')
                        @if(is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endif
                @endforeach
            </form>
        </div>
        <div class="col-md-3">
            <form>
                <div class="d-flex align-items-center">
                    <span>Search:</span>
                    <input type="text" class="form-control ms-1" name="search" value="{{ Request::get('search') }}" placeholder="{{ @$searchPlaceholder }}" onchange="this.form.submit()" />
                </div>
                @foreach (request()->except('search') as $key => $value)
                        @if(is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                @endforeach
            </form>
        </div>
    </div>
</div>