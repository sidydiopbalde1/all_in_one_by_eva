@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-beige-200 px-4 py-3 text-sm" aria-label="Pagination">
        <p class="text-gris-doux">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}</p>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="btn-secondaire btn-sm opacity-40"><x-icon name="chevron-left" class="size-4" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn-secondaire btn-sm" aria-label="Page précédente"><x-icon name="chevron-left" class="size-4" /></a>
            @endif
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn-secondaire btn-sm" aria-label="Page suivante"><x-icon name="chevron-right" class="size-4" /></a>
            @else
                <span class="btn-secondaire btn-sm opacity-40"><x-icon name="chevron-right" class="size-4" /></span>
            @endif
        </div>
    </nav>
@endif
