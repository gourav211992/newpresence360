<div class="d-flex justify-content-end mx-1 mt-50">
    @if ($paginator->hasPages())
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="paginate_button page-item previous disabled">
                    <a href="#" class="page-link">&nbsp;</a>
                </li>
            @else
                <li class="paginate_button page-item previous">
                    <a href="{{ $paginator->previousPageUrl() }}" class="page-link">&laquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="paginate_button page-item disabled">
                        <a href="#" class="page-link">{{ $element }}</a>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="paginate_button page-item active">
                                <a href="#" class="page-link">{{ $page }}</a>
                            </li>
                        @else
                            <li class="paginate_button page-item">
                                <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="paginate_button page-item next">
                    <a href="{{ $paginator->nextPageUrl() }}" class="page-link">&raquo;</a>
                </li>
            @else
                <li class="paginate_button page-item next disabled">
                    <a href="#" class="page-link">&nbsp;</a>
                </li>
            @endif
        </ul>
    @endif
</div>
