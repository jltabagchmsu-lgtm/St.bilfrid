@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="custom-pagination-container">
        <div class="pagination-info">
            <span>Page</span>
            <span class="pagination-highlight">{{ $paginator->currentPage() }}</span>
        </div>

        <ul class="pagination-nav-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="pagination-item disabled" aria-disabled="true">
                    <span class="pagination-link">&laquo; Prev</span>
                </li>
            @else
                <li class="pagination-item">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-link">&laquo; Prev</a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="pagination-item">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-link">Next &raquo;</a>
                </li>
            @else
                <li class="pagination-item disabled" aria-disabled="true">
                    <span class="pagination-link">Next &raquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
