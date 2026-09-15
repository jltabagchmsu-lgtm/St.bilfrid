@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="custom-pagination-container">
        <div class="pagination-info">
            <span>Showing</span>
            <span class="pagination-highlight">{{ $paginator->firstItem() ?? 0 }}</span>
            <span>to</span>
            <span class="pagination-highlight">{{ $paginator->lastItem() ?? 0 }}</span>
            <span>of</span>
            <span class="pagination-highlight">{{ $paginator->total() }}</span>
            <span>results</span>
        </div>

        <ul class="pagination-nav-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="pagination-item disabled" aria-disabled="true" aria-label="Previous">
                    <span class="pagination-link">&laquo; Prev</span>
                </li>
            @else
                <li class="pagination-item">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-link" aria-label="Previous">&laquo; Prev</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="pagination-item disabled" aria-disabled="true"><span class="pagination-link ellipsis">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="pagination-item active" aria-current="page"><span class="pagination-link">{{ $page }}</span></li>
                        @else
                            <li class="pagination-item"><a href="{{ $url }}" class="pagination-link">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="pagination-item">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-link" aria-label="Next">Next &raquo;</a>
                </li>
            @else
                <li class="pagination-item disabled" aria-disabled="true" aria-label="Next">
                    <span class="pagination-link">Next &raquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
