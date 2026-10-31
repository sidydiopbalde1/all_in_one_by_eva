@props(['label', 'value', 'icon' => null, 'hint' => null, 'tone' => 'default'])
<div class="carte carte-corps">
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm text-gris-doux">{{ $label }}</p>
        @if ($icon)
            <span class="rounded-lg bg-beige-100 p-2 text-bordeaux-700"><x-icon :name="$icon" class="size-4" /></span>
        @endif
    </div>
    <p @class(['mt-2 text-2xl font-semibold tracking-tight', 'text-red-700' => $tone === 'negatif', 'text-encre' => $tone !== 'negatif'])>{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-gris-doux">{{ $hint }}</p>
    @endif
</div>
