@props(['title', 'subtitle' => null])
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="titre-page">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-gris-doux">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
