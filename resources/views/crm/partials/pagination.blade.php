@if ($paginator->hasPages())
    <ul class="pagination">
        <!-- Previous Page Link -->
        <li class="paginate_button page-item previous {{ !$paginator->previousPageUrl() ? 'disabled' : '' }}">
            <a href="{{ $paginator->previousPageUrl() }}" class="page-link">&nbsp;</a>
        </li>

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="paginate_button page-item">
                    <a href="#" class="page-link">{{ $element }}</a>
                </li>
            @endif

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

        <!-- Previous Page Link -->
        <li class="{{ !$paginator->hasMorePages() ? 'disabled' : '' }} paginate_button page-item next">
            <a href="{{ $paginator->nextPageUrl() }}" class="page-link">&nbsp;</a>
        </li>
    </ul>
@endif