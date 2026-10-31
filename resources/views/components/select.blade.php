@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'required' => false])
@php($id = trim(str_replace(['[', ']', '.'], '_', $name), '_'))
@php($cle = str_replace(['[', ']'], ['.', ''], $name))
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }} @if ($required)<span class="text-bordeaux-600">*</span>@endif</label>
    <select id="{{ $id }}" name="{{ $name }}" @required($required) @class(['champ', 'champ-erreur' => $errors->has($cle)]) {{ $attributes->except('class') }}>
        @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) old($cle, $value) === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
    </select>
    @error($cle)<p class="erreur">{{ $message }}</p>@enderror
</div>
