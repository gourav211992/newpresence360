<div class="d-flex justify-content-between mx-0 row mt-50">
    <div class="col-sm-12 col-md-6" style="margin-top: 1rem; margin-bottom: 1.5rem; color: #b9b9c3;">
        Showing {{ ($paginator->currentpage() - 1) * $paginator->perpage() + 1 }}
            to {{ ($paginator->currentpage() - 1) * $paginator->perpage() + $paginator->count() }}
            of {{ $paginator->total() }} entries
    </div>
    @if ($paginator->hasPages())
        <div class="col-sm-12 col-md-6 d-flex justify-content-end" style="margin-top: 1rem; margin-bottom: 1.5rem;">
            <ul class="pagination">
                <li class="paginate_button page-item previous {{ !$paginator->previousPageUrl() ? 'disabled' : '' }}" id="DataTables_Table_0_previous">
                    <a href="{{ $paginator->previousPageUrl() }}" aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="0" class="page-link">&nbsp;</a>
                </li>
                @foreach ($elements as $element)
                    <!-- "Three Dots" Separator -->
                    @if (is_string($element))
                        <li class="paginate_button page-item">
                            <a href="#" class="page-link">{{ $element }}</a>
                        </li>
                    @endif

                    <!-- Array Of Links -->
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="paginate_button page-item active">
                                    <a href="#" aria-controls="DataTables_Table_0" data-dt-idx="{{ $page }}" tabindex="0" class="page-link">{{ $page }}</a>
                                </li>
                            @else
                                <li class="paginate_button page-item">
                                    <a href="{{ $url }}" aria-controls="DataTables_Table_0" data-dt-idx="{{ $page }}" tabindex="0" class="page-link">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif

                @endforeach
                <!-- Next Page Link -->
                <li class="paginate_button page-item next {{ !$paginator->hasMorePages() ? 'disabled' : ''}}" id="DataTables_Table_0_next">
                    <a href="{{ $paginator->nextPageUrl() }}" aria-controls="DataTables_Table_0" data-dt-idx="" tabindex="0" class="page-link">&nbsp;</a>
                </li>
            </ul>
        </div>
    @endif
</div>
