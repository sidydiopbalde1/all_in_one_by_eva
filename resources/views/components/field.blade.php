@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'required' => false])
@php($id = trim(str_replace(['[', ']', '.'], '_', $name), '_'))
@php($cle = str_replace(['[', ']'], ['.', ''], $name))
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }} @if ($required)<span class="text-bordeaux-600">*</span>@endif</label>
    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="3" @class(['champ', 'champ-erreur' => $errors->has($cle)]) {{ $attributes->except('class') }}>{{ old($cle, $value) }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($cle, $value) }}" @required($required)
               @class(['champ', 'champ-erreur' => $errors->has($cle)]) {{ $attributes->except('class') }}>
    @endif
    @if ($help)<p class="aide">{{ $help }}</p>@endif
    @error($cle)<p class="erreur">{{ $message }}</p>@enderror
</div>
