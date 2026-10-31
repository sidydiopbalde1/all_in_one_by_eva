<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Gestion' }} · All in One by Eva</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
@php
    $navigation = array_filter([
        ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'Tableau de bord', 'icon' => 'layout-dashboard', 'can' => 'rdv.gerer'],
        ['route' => 'planning', 'match' => 'planning', 'label' => 'Mon planning', 'icon' => 'calendar-check', 'show' => auth()->user()->employe !== null],
        ['route' => 'rendez-vous.index', 'match' => 'rendez-vous.*', 'label' => 'Rendez-vous', 'icon' => 'calendar-days', 'can' => 'rdv.gerer'],
        ['route' => 'clients.index', 'match' => 'clients.*', 'label' => 'Clients', 'icon' => 'users', 'can' => 'clients.gerer'],
        ['route' => 'catalogue', 'match' => 'catalogue|services.*|forfaits.*', 'label' => 'Prestations', 'icon' => 'scissors'],
        ['route' => 'paiements.index', 'match' => 'paiements.*', 'label' => 'Caisse', 'icon' => 'wallet', 'can' => 'paiements.enregistrer'],
        ['route' => 'employes.index', 'match' => 'employes.*', 'label' => 'Équipe', 'icon' => 'user-cog', 'can' => 'employes.gerer'],
        ['route' => 'parametres.edit', 'match' => 'parametres.*', 'label' => 'Paramètres', 'icon' => 'settings', 'can' => 'parametres.gerer'],
    ], fn ($item) => ($item['show'] ?? true) && (! isset($item['can']) || auth()->user()->can($item['can'])));
@endphp

<div class="lg:flex">
    {{-- Barre latérale (ordinateur) / tiroir (mobile) --}}
    <aside class="bg-bordeaux-700 text-beige-100 print:hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
        <div class="flex items-center justify-between px-5 py-4 lg:px-6 lg:py-8">
            <a href="{{ route('dashboard') }}" class="block">
                <span class="font-display text-2xl leading-none font-semibold text-white lg:text-[1.7rem]">All in One by Eva</span>
                <span class="mt-1 hidden text-[11px] tracking-[0.18em] text-beige-200/70 uppercase lg:block">Beauté &amp; bien-être</span>
            </a>
            <button type="button" class="rounded-lg p-2 hover:bg-white/10 lg:hidden" data-toggle="menu-principal" aria-label="Ouvrir le menu">
                <x-icon name="menu" />
            </button>
        </div>

        <nav id="menu-principal" class="hidden space-y-1 px-3 pb-4 lg:block lg:flex-1">
            @foreach ($navigation as $item)
                @php($actif = request()->routeIs(...explode('|', $item['match'])))
                <a href="{{ route($item['route']) }}" @class([
                    'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm transition',
                    'bg-ivoire font-semibold text-bordeaux-700' => $actif,
                    'text-beige-100/85 hover:bg-white/10 hover:text-white' => ! $actif,
                ])>
                    <x-icon :name="$item['icon']" class="size-[18px]" />
                    {{ $item['label'] }}
                </a>
            @endforeach

            <div class="mt-6 border-t border-white/10 pt-4 lg:hidden">
                @include('partials.user')
            </div>
        </nav>

        <div class="hidden border-t border-white/10 px-5 py-5 lg:block">
            @include('partials.user')
        </div>
    </aside>

    <main class="min-w-0 flex-1 lg:pl-64 print:pl-0">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
            <x-flash />
            {{ $slot }}
        </div>
    </main>
</div>
</body>
</html>
