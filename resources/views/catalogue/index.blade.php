<x-layouts.app title="Prestations">
    <x-page-header title="Prestations & tarifs" subtitle="Catalogue des services et forfaits du salon.">
        @can('catalogue.gerer')
            <x-slot:actions>
                <a href="{{ route('forfaits.create') }}" class="btn-secondaire"><x-icon name="sparkles" class="size-4" /> Nouveau forfait</a>
                <a href="{{ route('services.create') }}" class="btn-primaire"><x-icon name="plus" class="size-4" /> Nouvelle prestation</a>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ($categories as $categorie)
            <section class="carte">
                <h2 class="border-b border-beige-200 px-6 py-4 font-display text-xl font-semibold">{{ $categorie->nom }}</h2>
                @forelse ($categorie->services as $service)
                    <div @class(['flex items-center gap-4 border-b border-beige-200/70 px-6 py-3 last:border-0', 'opacity-50' => ! $service->is_active])>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">{{ $service->nom }}</p>
                            <p class="text-xs text-gris-doux">{{ $service->duree_minutes }} min · {{ $service->zone->label() }}{{ $service->is_active ? '' : ' · inactif' }}</p>
                        </div>
                        <span class="text-sm font-semibold whitespace-nowrap">@fcfa($service->prix)</span>
                        @can('catalogue.gerer')
                            <a href="{{ route('services.edit', $service) }}" class="rounded-lg p-1.5 text-gris-doux hover:bg-beige-100 hover:text-bordeaux-700" title="Modifier"><x-icon name="pencil" class="size-4" /></a>
                        @endcan
                    </div>
                @empty
                    <p class="px-6 py-4 text-sm text-gris-doux">Aucune prestation dans cette catégorie.</p>
                @endforelse
            </section>
        @endforeach
    </div>

    <section class="carte mt-6">
        <h2 class="border-b border-beige-200 px-6 py-4 font-display text-xl font-semibold">Forfaits combinés</h2>
        @forelse ($forfaits as $forfait)
            <div @class(['flex items-center gap-4 border-b border-beige-200/70 px-6 py-3 last:border-0', 'opacity-50' => ! $forfait->is_active])>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium">{{ $forfait->nom }}</p>
                    <p class="truncate text-xs text-gris-doux">{{ $forfait->services->pluck('nom')->join(' + ') }} · {{ $forfait->dureeTotale() }} min</p>
                </div>
                <span class="text-right text-sm whitespace-nowrap">
                    <span class="font-semibold">@fcfa($forfait->prix)</span>
                    @if ($forfait->prixDetail() > $forfait->prix)<span class="block text-xs text-gris-doux line-through">@fcfa($forfait->prixDetail())</span>@endif
                </span>
                @can('catalogue.gerer')
                    <a href="{{ route('forfaits.edit', $forfait) }}" class="rounded-lg p-1.5 text-gris-doux hover:bg-beige-100 hover:text-bordeaux-700" title="Modifier"><x-icon name="pencil" class="size-4" /></a>
                @endcan
            </div>
        @empty
            <x-empty icon="sparkles" title="Aucun forfait" />
        @endforelse
    </section>
</x-layouts.app>
