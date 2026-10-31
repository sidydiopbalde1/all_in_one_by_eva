@props(['tone' => 'stone'])
@php
    $classes = [
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'sky' => 'bg-sky-50 text-sky-800 ring-sky-200',
        'violet' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'emerald' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'red' => 'bg-red-50 text-red-700 ring-red-200',
        'bordeaux' => 'bg-bordeaux-50 text-bordeaux-700 ring-bordeaux-200',
        'stone' => 'bg-stone-100 text-stone-600 ring-stone-200',
    ][$tone] ?? 'bg-stone-100 text-stone-600 ring-stone-200';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {$classes}"]) }}>{{ $slot }}</span>
