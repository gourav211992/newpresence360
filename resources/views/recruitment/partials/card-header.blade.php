<div class="p-2">
    <div class="row justify-content-between">
        <div class="col-md-10 mb-1 mb-sm-0">
            <form>
                @php
                    $pageLengths = App\Helpers\CommonHelper::PAGE_LENGTHS;
                @endphp
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
                        @if (is_array($value))
                            @foreach ($value as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endif
                @endforeach
            </form>
        </div>
        <div class="col-md-2">
            <form>
                <div id="DataTables_Table_0_filter" class="dataTables_filter">
                    <label><input type="search" class="form-control" placeholder="Search..."
                            aria-controls="DataTables_Table_0" name="search" value="{{ Request::get('search') }}"
                            placeholder="{{ @$searchPlaceholder }}" onchange="this.form.submit()"></label>
                </div>
                @foreach (request()->except('search') as $key => $value)
                    @if (is_array($value))
                        @foreach ($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
            </form>
        </div>
    </div>
</div>
