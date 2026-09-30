@if ($paginator->hasPages())
    <style>
        .custom-pagination-container {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 14px !important;
            width: 100% !important;
            margin-top: 20px !important;
            padding: 14px 20px !important;
            background: #ffffff !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 12px !important;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04) !important;
            box-sizing: border-box !important;
        }

        .custom-pagination-container * {
            box-sizing: border-box;
        }

        .custom-pagination-container .pagination-info {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            font-size: 0.85rem !important;
            color: #475569 !important;
            font-weight: 500 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .custom-pagination-container .pagination-info-icon {
            color: #94a3b8;
            flex-shrink: 0;
            margin-right: 2px;
        }

        .custom-pagination-container .pagination-highlight {
            display: inline-block !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            background: #f1f5f9 !important;
            padding: 2px 8px !important;
            border-radius: 6px !important;
            border: 1px solid #e2e8f0 !important;
            font-size: 0.825rem !important;
            line-height: 1.3 !important;
            font-family: 'JetBrains Mono', monospace, ui-monospace, sans-serif !important;
        }

        .custom-pagination-container .pagination-nav-list {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            flex-wrap: wrap !important;
        }

        .custom-pagination-container .pagination-item {
            display: inline-flex !important;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .custom-pagination-container .pagination-link {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            min-width: 36px !important;
            height: 36px !important;
            padding: 0 12px !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: #334155 !important;
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 9px !important;
            text-decoration: none !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03) !important;
            user-select: none !important;
            cursor: pointer !important;
            line-height: 1 !important;
        }

        .custom-pagination-container .pagination-link:hover {
            color: #dc2626 !important;
            border-color: #dc2626 !important;
            background: #fef2f2 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 3px 8px rgba(220, 38, 38, 0.15) !important;
        }

        .custom-pagination-container .pagination-item.active .pagination-link {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            border-color: #dc2626 !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35) !important;
            transform: scale(1.04) !important;
            pointer-events: none !important;
            cursor: default !important;
        }

        .custom-pagination-container .pagination-item.disabled .pagination-link {
            color: #94a3b8 !important;
            background: #f8fafc !important;
            border-color: #e2e8f0 !important;
            cursor: not-allowed !important;
            opacity: 0.65 !important;
            box-shadow: none !important;
            pointer-events: none !important;
            transform: none !important;
        }

        .custom-pagination-container .pagination-link.ellipsis {
            border: none !important;
            background: transparent !important;
            color: #94a3b8 !important;
            font-weight: 800 !important;
            letter-spacing: 2px !important;
            min-width: 24px !important;
            padding: 0 4px !important;
            box-shadow: none !important;
            cursor: default !important;
        }

        @media (max-width: 640px) {
            .custom-pagination-container {
                flex-direction: column !important;
                align-items: center !important;
                text-align: center !important;
                padding: 12px 14px !important;
            }
            .custom-pagination-container .pagination-nav-list {
                justify-content: center !important;
            }
        }
    </style>

    <nav role="navigation" aria-label="Pagination Navigation" class="custom-pagination-container">
        <div class="pagination-info">
            <svg class="pagination-info-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
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
                    <span class="pagination-link">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        <span>Previous</span>
                    </span>
                </li>
            @else
                <li class="pagination-item">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-link" aria-label="Previous">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        <span>Previous</span>
                    </a>
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
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-link" aria-label="Next">
                        <span>Next</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                </li>
            @else
                <li class="pagination-item disabled" aria-disabled="true" aria-label="Next">
                    <span class="pagination-link">
                        <span>Next</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
