@props(['icon' => 'sparkles', 'title', 'text' => null])
<div class="px-6 py-14 text-center">
    <span class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-beige-100 text-bordeaux-700"><x-icon :name="$icon" /></span>
    <p class="font-medium text-encre">{{ $title }}</p>
    @if ($text)<p class="mt-1 text-sm text-gris-doux">{{ $text }}</p>@endif
    {{ $slot }}
</div>
