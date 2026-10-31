<x-layouts.app title="Équipe">
    <x-page-header title="Équipe" subtitle="Professionnel·les, horaires et prestations du mois.">
        <x-slot:actions>
            <a href="{{ route('employes.create') }}" class="btn-primaire"><x-icon name="plus" class="size-4" /> Nouveau membre</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($employes as $employe)
            <article @class(['carte carte-corps', 'opacity-60' => ! $employe->is_active])>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-encre">{{ $employe->nom_complet }}</h2>
                        <p class="text-sm text-gris-doux">{{ $employe->fonction }}</p>
                    </div>
                    <a href="{{ route('employes.edit', $employe) }}" class="rounded-lg p-1.5 text-gris-doux hover:bg-beige-100 hover:text-bordeaux-700" title="Modifier"><x-icon name="pencil" class="size-4" /></a>
                </div>
                @if ($employe->specialites)<p class="mt-3 text-sm">{{ $employe->specialites }}</p>@endif
                <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-beige-100/60 p-3 text-sm">
                    <div><p class="text-xs text-gris-doux">Prestations du mois</p><p class="font-semibold">{{ $employe->prestations_mois }}</p></div>
                    <div><p class="text-xs text-gris-doux">Montant réalisé</p><p class="font-semibold">@fcfa($employe->montant_mois ?? 0)</p></div>
                </div>
                <p class="mt-4 text-xs text-gris-doux">
                    {{ $employe->disponibilites->map(fn ($d) => mb_substr($d->libelleJour(), 0, 3).' '.substr($d->heure_debut, 0, 5).'–'.substr($d->heure_fin, 0, 5))->join(' · ') ?: 'Aucun horaire défini' }}
                </p>
            </article>
        @empty
            <div class="carte md:col-span-2 xl:col-span-3"><x-empty icon="user-cog" title="Aucun membre de l'équipe" /></div>
        @endforelse
    </div>
</x-layouts.app>
