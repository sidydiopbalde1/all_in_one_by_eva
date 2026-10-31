@props(['name', 'label', 'checked' => false, 'help' => null])
<label class="flex items-start gap-3">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked))
           class="mt-0.5 size-4 rounded border-beige-300 text-bordeaux-700 focus:ring-bordeaux-500">
    <span>
        <span class="text-sm font-medium">{{ $label }}</span>
        @if ($help)<span class="block text-xs text-gris-doux">{{ $help }}</span>@endif
    </span>
</label>
