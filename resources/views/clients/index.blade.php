<x-layouts.app title="Clients">
    <x-page-header title="Clients" subtitle="Fiches, historique et fidélité de la clientèle.">
        <x-slot:actions>
            @can('clients.exporter')
                <a href="{{ route('clients.export', request()->only('q', 'filtre')) }}" class="btn-secondaire"><x-icon name="download" class="size-4" /> Exporter CSV</a>
            @endcan
            <a href="{{ route('clients.create') }}" class="btn-primaire"><x-icon name="plus" class="size-4" /> Nouveau client</a>
        </x-slot:actions>
    </x-page-header>

    <div class="carte">
        <div class="flex flex-col gap-3 border-b border-beige-200 p-4 lg:flex-row lg:items-center lg:justify-between">
            <nav class="flex flex-wrap gap-1 rounded-xl bg-beige-100/70 p-1">
                @foreach (['' => 'Tous', 'fideles' => 'Plus fidèles', 'inactifs' => 'À relancer', 'promo' => 'Consentement promo', 'archives' => 'Archivés'] as $cle => $libelle)
                    <a href="{{ route('clients.index', array_filter(['filtre' => $cle, 'q' => request('q')])) }}" @class(['onglet', 'onglet-actif' => $filtre === $cle])>{{ $libelle }}</a>
                @endforeach
            </nav>
            <form method="GET" class="relative lg:w-80">
                <input type="hidden" name="filtre" value="{{ $filtre }}">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gris-doux" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Nom ou téléphone…" class="champ pl-9" aria-label="Rechercher un client">
            </form>
        </div>

        @if ($clients->isEmpty())
            <x-empty icon="users" title="Aucun client trouvé" text="Modifiez la recherche ou ajoutez un nouveau client." />
        @else
            <div class="overflow-x-auto">
                <table class="tableau">
                    <thead>
                        <tr><th>Client</th><th>Téléphone</th><th class="hidden md:table-cell">Visites</th><th class="hidden md:table-cell">Dernière visite</th><th>Statut</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($clients as $client)
                            <tr>
                                <td>
                                    <a href="{{ route('clients.show', $client) }}" class="font-medium hover:text-bordeaux-700 hover:underline">{{ $client->nom_complet }}</a>
                                    @if ($client->consentement_promo)<span class="ml-1 text-xs text-gris-doux" title="Accepte les promotions">· promo</span>@endif
                                </td>
                                <td class="whitespace-nowrap text-gris-doux">@tel($client->telephone)</td>
                                <td class="hidden md:table-cell">{{ $client->visites_count }}</td>
                                <td class="hidden text-gris-doux md:table-cell">{{ $client->derniere_visite ? \Illuminate\Support\Carbon::parse($client->derniere_visite)->format('d/m/Y') : '—' }}</td>
                                <td><x-badge :tone="$client->statut->value === 'actif' ? 'emerald' : 'stone'">{{ $client->statut->label() }}</x-badge></td>
                                <td class="text-right">
                                    @can('whatsapp.envoyer')
                                        <a href="{{ route('whatsapp.client', [$client, 'fidelisation']) }}" target="_blank" rel="noopener" class="inline-flex rounded-lg p-2 text-[#1f8f54] hover:bg-emerald-50" title="Message WhatsApp"><x-icon name="message-circle" class="size-4" /></a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $clients->links() }}
        @endif
    </div>
</x-layouts.app>
