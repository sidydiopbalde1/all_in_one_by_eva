@php
    $pas = ['jour' => 'addDay', 'semaine' => 'addWeek', 'mois' => 'addMonth'][$vue];
    $recul = ['jour' => 'subDay', 'semaine' => 'subWeek', 'mois' => 'subMonth'][$vue];
    $lien = fn (array $params) => route($route, array_filter([...request()->only('employe', 'vue'), ...$params]));
    $titre = match ($vue) {
        'jour' => ucfirst($date->translatedFormat('l j F Y')),
        'semaine' => 'Semaine du '.$debut->translatedFormat('j F').' au '.$fin->translatedFormat('j F Y'),
        'mois' => ucfirst($date->translatedFormat('F Y')),
    };
    $estPlanning = $route === 'planning';
@endphp
<x-layouts.app :title="$estPlanning ? 'Mon planning' : 'Rendez-vous'">
    <x-page-header :title="$estPlanning ? 'Mon planning' : 'Rendez-vous'" :subtitle="$titre">
        @unless ($estPlanning)
            <x-slot:actions>
                <a href="{{ route('rendez-vous.create', ['date' => $date->toDateString()]) }}" class="btn-primaire"><x-icon name="plus" class="size-4" /> Nouveau rendez-vous</a>
            </x-slot:actions>
        @endunless
    </x-page-header>

    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ $lien(['date' => $date->$recul()->toDateString()]) }}" class="btn-secondaire btn-sm" aria-label="Précédent"><x-icon name="chevron-left" class="size-4" /></a>
            <a href="{{ $lien(['date' => today()->toDateString()]) }}" class="btn-secondaire btn-sm">Aujourd'hui</a>
            <a href="{{ $lien(['date' => $date->$pas()->toDateString()]) }}" class="btn-secondaire btn-sm" aria-label="Suivant"><x-icon name="chevron-right" class="size-4" /></a>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @unless ($estPlanning)
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="vue" value="{{ $vue }}"><input type="hidden" name="date" value="{{ $date->toDateString() }}">
                    <select name="employe" class="champ py-1.5" onchange="this.form.submit()" aria-label="Filtrer par professionnel">
                        <option value="">Toute l'équipe</option>
                        @foreach ($employes as $e)
                            <option value="{{ $e->id }}" @selected($employeId === $e->id)>{{ $e->nom_complet }}</option>
                        @endforeach
                    </select>
                </form>
            @endunless
            <nav class="flex gap-1 rounded-xl bg-beige-100/70 p-1">
                @foreach (['jour' => 'Jour', 'semaine' => 'Semaine', 'mois' => 'Mois'] as $cle => $libelle)
                    <a href="{{ $lien(['vue' => $cle, 'date' => $date->toDateString()]) }}" @class(['onglet', 'onglet-actif' => $vue === $cle])>{{ $libelle }}</a>
                @endforeach
            </nav>
        </div>
    </div>

    @if ($vue === 'jour')
        @php($colonnes = $employeId ? $employes->where('id', $employeId) : $employes)
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($colonnes as $employe)
                @php($liste = $rendezVous->where('employe_id', $employe->id))
                <section class="carte">
                    <h2 class="flex items-center justify-between border-b border-beige-200 px-5 py-3 text-sm font-semibold text-encre">
                        {{ $employe->nom_complet }} <span class="text-xs font-normal text-gris-doux">{{ $liste->count() }} rdv</span>
                    </h2>
                    <div class="space-y-2 p-3">
                        @forelse ($liste as $rdv)
                            @include('rendez-vous.partials.carte', ['rdv' => $rdv])
                        @empty
                            <p class="px-2 py-6 text-center text-sm text-gris-doux">Journée libre</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    @elseif ($vue === 'semaine')
        <div class="grid gap-3 md:grid-cols-7">
            @foreach (\Carbon\CarbonPeriod::create($debut, $fin->startOfDay()) as $jour)
                <section @class(['carte min-h-40', 'ring-2 ring-bordeaux-200' => $jour->isToday()])>
                    <a href="{{ $lien(['vue' => 'jour', 'date' => $jour->toDateString()]) }}" class="block border-b border-beige-200 px-3 py-2 text-xs font-semibold text-gris-doux uppercase hover:text-bordeaux-700">
                        {{ $jour->locale('fr')->translatedFormat('D j') }}
                    </a>
                    <div class="space-y-2 p-2">
                        @foreach ($parJour->get($jour->toDateString(), []) as $rdv)
                            @include('rendez-vous.partials.carte', ['rdv' => $rdv, 'compact' => true])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="carte overflow-hidden">
            <div class="grid grid-cols-7 border-b border-beige-200 bg-beige-100/50 text-center text-xs font-semibold text-gris-doux uppercase">
                @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $j)<div class="py-2">{{ $j }}</div>@endforeach
            </div>
            <div class="grid grid-cols-7">
                @foreach (\Carbon\CarbonPeriod::create($debut, $fin->startOfDay()) as $jour)
                    @php($liste = collect($parJour->get($jour->toDateString(), [])))
                    <a href="{{ $lien(['vue' => 'jour', 'date' => $jour->toDateString()]) }}" @class([
                        'min-h-24 border-r border-b border-beige-200/70 p-2 text-xs hover:bg-beige-100/40',
                        'bg-stone-50/60 text-gris-doux' => ! $jour->isSameMonth($date),
                    ])>
                        <span @class(['inline-flex size-6 items-center justify-center rounded-full font-semibold', 'bg-bordeaux-700 text-white' => $jour->isToday()])>{{ $jour->day }}</span>
                        @foreach ($liste->take(3) as $rdv)
                            <span class="mt-1 block truncate rounded bg-bordeaux-50 px-1.5 py-0.5 text-bordeaux-800">{{ $rdv->debut->format('H:i') }} {{ $rdv->client->prenom }}</span>
                        @endforeach
                        @if ($liste->count() > 3)<span class="mt-1 block text-gris-doux">+{{ $liste->count() - 3 }} autres</span>@endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</x-layouts.app>
