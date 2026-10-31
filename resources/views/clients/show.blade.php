<x-layouts.app :title="$client->nom_complet">
    <a href="{{ route('clients.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gris-doux hover:text-bordeaux-700"><x-icon name="arrow-left" class="size-4" /> Clients</a>

    <x-page-header :title="$client->nom_complet" :subtitle="'Client depuis le '.$client->date_inscription->format('d/m/Y')">
        <x-slot:actions>
            @can('whatsapp.envoyer')
                <a href="{{ route('whatsapp.client', [$client, 'promotion']) }}" target="_blank" rel="noopener" class="btn-whatsapp"><x-icon name="message-circle" class="size-4" /> Envoyer un message WhatsApp</a>
            @endcan
            <a href="{{ route('rendez-vous.create', ['client' => $client->id]) }}" class="btn-primaire"><x-icon name="plus" class="size-4" /> Rendez-vous</a>
            <a href="{{ route('clients.edit', $client) }}" class="btn-secondaire"><x-icon name="pencil" class="size-4" /> Modifier</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <section class="carte carte-corps space-y-3 text-sm">
                <h2 class="font-display text-xl font-semibold">Coordonnées</h2>
                <p class="flex items-center gap-2"><x-icon name="phone" class="size-4 text-gris-doux" /> @tel($client->telephone)</p>
                @if ($client->whatsapp)<p class="flex items-center gap-2"><x-icon name="message-circle" class="size-4 text-gris-doux" /> @tel($client->whatsapp)</p>@endif
                @if ($client->email)<p>{{ $client->email }}</p>@endif
                <p class="flex items-center gap-2"><x-icon name="cake" class="size-4 text-gris-doux" /> {{ $client->date_naissance?->locale('fr')->translatedFormat('j F') ?? 'Date de naissance non renseignée' }}</p>
                <p>Préférence : <span class="font-medium">{{ ['whatsapp' => 'WhatsApp', 'appel' => 'Appel', 'sms' => 'SMS', 'email' => 'E-mail'][$client->preference_communication] ?? $client->preference_communication }}</span></p>
                <p>Promotions : <x-badge :tone="$client->consentement_promo ? 'emerald' : 'stone'">{{ $client->consentement_promo ? 'Accepte' : 'Refuse' }}</x-badge></p>
                @if ($client->preferences_prestations)<p class="text-gris-doux">Aime : {{ $client->preferences_prestations }}</p>@endif
                @if ($client->notes)<p class="rounded-lg bg-beige-100/60 p-3 text-gris-doux">{{ $client->notes }}</p>@endif
            </section>

            <section class="carte carte-corps">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-xl font-semibold">Fidélité</h2>
                    <span class="text-2xl font-semibold text-bordeaux-700">{{ $solde }} <span class="text-sm font-normal text-gris-doux">pts</span></span>
                </div>
                <ul class="mt-4 space-y-2">
                    @foreach ($recompenses as $recompense)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-beige-200 px-3 py-2.5 text-sm">
                            <span><span class="font-medium">{{ $recompense->libelle }}</span><span class="block text-xs text-gris-doux">{{ $recompense->seuil_points }} points</span></span>
                            @if ($solde >= $recompense->seuil_points)
                                <form method="POST" action="{{ route('fidelite.utiliser', [$client, $recompense]) }}" data-confirm="Utiliser cette récompense ?">
                                    @csrf
                                    <button class="btn-primaire btn-sm"><x-icon name="gift" class="size-3.5" /> Utiliser</button>
                                </form>
                            @else
                                <span class="text-xs text-gris-doux">encore {{ $recompense->seuil_points - $solde }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @can('fidelite.corriger')
                    <details class="mt-4 text-sm">
                        <summary class="cursor-pointer text-bordeaux-700">Corriger le solde</summary>
                        <form method="POST" action="{{ route('fidelite.corriger', $client) }}" class="mt-3 space-y-3">
                            @csrf
                            <x-field name="points" type="number" label="Points (+ ou −)" required />
                            <x-field name="motif" label="Motif" required />
                            <button class="btn-secondaire btn-sm">Enregistrer la correction</button>
                        </form>
                    </details>
                @endcan
            </section>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="grid grid-cols-3 gap-4">
                <x-stat label="Visites" :value="$client->rendezVous->where('statut', \App\Enums\StatutRendezVous::Termine)->count()" />
                <x-stat label="Total payé" :value="\App\Support\Money::fcfa($totalPaye)" />
                <x-stat label="Dernière visite" :value="$derniereVisite?->format('d/m/Y') ?? '—'" />
            </div>

            <section class="carte">
                <h2 class="border-b border-beige-200 px-6 py-4 font-display text-xl font-semibold">Rendez-vous</h2>
                @forelse ($client->rendezVous as $rdv)
                    <a href="{{ route('rendez-vous.show', $rdv) }}" class="flex items-center gap-4 border-b border-beige-200/70 px-6 py-3 last:border-0 hover:bg-beige-100/40">
                        <span class="w-24 shrink-0 text-sm"><span class="block font-medium">{{ $rdv->debut->format('d/m/Y') }}</span><span class="text-xs text-gris-doux">{{ $rdv->debut->format('H:i') }}</span></span>
                        <span class="min-w-0 flex-1 truncate text-sm">{{ $rdv->libellePrestations() }} <span class="text-gris-doux">· {{ $rdv->employe->prenom }}</span></span>
                        <span class="hidden text-sm sm:block">@fcfa($rdv->prix_prevu)</span>
                        <x-statut-rdv :statut="$rdv->statut" />
                    </a>
                @empty
                    <x-empty icon="calendar-days" title="Aucun rendez-vous" />
                @endforelse
            </section>

            <section class="carte">
                <h2 class="border-b border-beige-200 px-6 py-4 font-display text-xl font-semibold">Paiements</h2>
                @forelse ($client->paiements as $paiement)
                    <div @class(['flex items-center justify-between border-b border-beige-200/70 px-6 py-3 text-sm last:border-0', 'opacity-50' => $paiement->estAnnule()])>
                        <span>{{ $paiement->paye_le->format('d/m/Y') }} · {{ $paiement->mode->libelle }}</span>
                        <span class="font-medium {{ $paiement->estAnnule() ? 'line-through' : '' }}">@fcfa($paiement->montant_paye)</span>
                    </div>
                @empty
                    <x-empty icon="wallet" title="Aucun paiement" />
                @endforelse
            </section>

            <section class="carte">
                <h2 class="border-b border-beige-200 px-6 py-4 font-display text-xl font-semibold">Historique des points</h2>
                @forelse ($client->mouvementsPoints as $mvt)
                    <div class="flex items-center justify-between border-b border-beige-200/70 px-6 py-3 text-sm last:border-0">
                        <span>{{ $mvt->created_at->format('d/m/Y') }} · {{ $mvt->motif }}</span>
                        <span @class(['font-semibold', 'text-emerald-700' => $mvt->points > 0, 'text-red-700' => $mvt->points < 0])>{{ $mvt->points > 0 ? '+' : '' }}{{ $mvt->points }}</span>
                    </div>
                @empty
                    <x-empty icon="star" title="Aucun point pour le moment" />
                @endforelse
            </section>

            @if ($client->statut !== \App\Enums\StatutClient::Archive)
                <form method="POST" action="{{ route('clients.archiver', $client) }}" class="text-right" data-confirm="Archiver ce client ? Son historique sera conservé.">
                    @csrf @method('PATCH')
                    <button class="btn-danger btn-sm"><x-icon name="archive" class="size-3.5" /> Archiver le client</button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.app>
