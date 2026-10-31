<x-layouts.app title="Tableau de bord">
    <x-page-header title="Bonjour {{ str(auth()->user()->name)->before(' ') }}" :subtitle="ucfirst(now()->locale('fr')->translatedFormat('l j F Y'))">
        <x-slot:actions>
            <a href="{{ route('clients.create') }}" class="btn-secondaire"><x-icon name="users" class="size-4" /> Nouveau client</a>
            <a href="{{ route('rendez-vous.create') }}" class="btn-primaire"><x-icon name="plus" class="size-4" /> Nouveau rendez-vous</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Clients enregistrés" :value="number_format($stats['clients_total'], 0, ',', ' ')" icon="users" :hint="$stats['clients_nouveaux_mois'].' nouveau(x) ce mois-ci'" />
        <x-stat label="Rendez-vous du jour" :value="$stats['rdv_du_jour']" icon="calendar-days" :hint="$stats['rdv_a_venir'].' à venir'" />
        <x-stat label="Prestations terminées" :value="$stats['prestations_terminees_mois']" icon="check" hint="Ce mois-ci" />
        <x-stat label="Clients à relancer" :value="$stats['clients_a_relancer']" icon="message-circle" :hint="'Sans visite depuis '.$stats['semaines_inactivite'].' semaines'" />
    </div>

    @if ($voirFinances)
        <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-stat label="Chiffre d'affaires du jour" :value="\App\Support\Money::fcfa($stats['ca_jour'])" icon="coins" />
            <x-stat label="Chiffre d'affaires du mois" :value="\App\Support\Money::fcfa($stats['ca_mois'])" icon="trending-up" />
            <x-stat label="Dépenses du mois" :value="\App\Support\Money::fcfa($stats['depenses_mois'])" icon="receipt" />
            <x-stat label="Résultat estimé du mois" :value="\App\Support\Money::fcfa($stats['resultat_mois'])" icon="wallet"
                    :tone="$stats['resultat_mois'] < 0 ? 'negatif' : 'default'" hint="Indicateur de gestion, non comptable" />
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="carte lg:col-span-2">
            <div class="flex items-center justify-between border-b border-beige-200 px-6 py-4">
                <h2 class="font-display text-xl font-semibold">Rendez-vous d'aujourd'hui</h2>
                <a href="{{ route('rendez-vous.index') }}" class="text-sm font-medium text-bordeaux-700 hover:underline">Voir le calendrier</a>
            </div>
            @forelse ($stats['rdv_aujourdhui'] as $rdv)
                <a href="{{ route('rendez-vous.show', $rdv) }}" class="flex items-center gap-4 border-b border-beige-200/70 px-6 py-3.5 last:border-0 hover:bg-beige-100/40">
                    <span class="w-14 shrink-0 text-sm font-semibold text-bordeaux-700">{{ $rdv->debut->format('H:i') }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-medium">{{ $rdv->client->nom_complet }}</span>
                        <span class="block truncate text-xs text-gris-doux">{{ $rdv->libellePrestations() }} · {{ $rdv->employe->prenom }}</span>
                    </span>
                    <x-statut-rdv :statut="$rdv->statut" />
                </a>
            @empty
                <x-empty icon="calendar-days" title="Aucun rendez-vous aujourd'hui" text="Les rendez-vous de la journée apparaîtront ici." />
            @endforelse
        </section>

        <section class="carte">
            <div class="border-b border-beige-200 px-6 py-4">
                <h2 class="font-display text-xl font-semibold">Anniversaires</h2>
                <p class="text-xs text-gris-doux">Aujourd'hui et 7 prochains jours</p>
            </div>
            @forelse ($stats['anniversaires'] as $client)
                <div class="flex items-center gap-3 border-b border-beige-200/70 px-6 py-3 last:border-0">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-bordeaux-50 text-bordeaux-700"><x-icon name="cake" class="size-4" /></span>
                    <span class="min-w-0 flex-1">
                        <a href="{{ route('clients.show', $client) }}" class="block truncate text-sm font-medium hover:underline">{{ $client->nom_complet }}</a>
                        <span class="text-xs text-gris-doux">{{ $client->prochain_anniversaire->isToday() ? "Aujourd'hui 🎉" : $client->prochain_anniversaire->locale('fr')->translatedFormat('l j F') }}</span>
                    </span>
                    @can('whatsapp.envoyer')
                        <a href="{{ route('whatsapp.client', [$client, 'anniversaire']) }}" target="_blank" rel="noopener" class="rounded-lg p-2 text-[#1f8f54] hover:bg-emerald-50" title="Souhaiter l'anniversaire sur WhatsApp">
                            <x-icon name="message-circle" class="size-4" />
                        </a>
                    @endcan
                </div>
            @empty
                <x-empty icon="cake" title="Aucun anniversaire cette semaine" />
            @endforelse
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        @if ($voirFinances)
            <section class="carte carte-corps lg:col-span-2">
                <h2 class="font-display text-xl font-semibold">Chiffre d'affaires encaissé</h2>
                <p class="text-xs text-gris-doux">6 derniers mois · paiements annulés exclus</p>
                <div class="mt-4 h-64">
                    <canvas data-chart="{{ json_encode(['type' => 'bar', 'labels' => $evolution['labels'], 'values' => $evolution['valeurs'], 'money' => true]) }}" aria-label="Graphique du chiffre d'affaires"></canvas>
                </div>
            </section>
        @endif

        <section @class(['carte carte-corps', 'lg:col-span-3' => ! $voirFinances])>
            <h2 class="font-display text-xl font-semibold">Prestations les plus demandées</h2>
            @if ($stats['services_populaires']->isEmpty())
                <x-empty icon="star" title="Pas encore de prestation terminée" />
            @else
                <div class="mt-4 h-64">
                    <canvas data-chart="{{ json_encode(['type' => 'doughnut', 'labels' => $stats['services_populaires']->pluck('libelle'), 'values' => $stats['services_populaires']->pluck('total')]) }}" aria-label="Répartition des prestations"></canvas>
                </div>
            @endif
        </section>
    </div>

    @if ($stats['alertes_stock']->isNotEmpty())
        <section class="carte mt-6 border-amber-200">
            <div class="flex items-center gap-2 border-b border-amber-100 bg-amber-50/60 px-6 py-3 text-amber-900">
                <x-icon name="alert-triangle" class="size-4" />
                <h2 class="text-sm font-semibold text-amber-900">Alertes de stock</h2>
            </div>
            <ul class="divide-y divide-beige-200/70">
                @foreach ($stats['alertes_stock'] as $produit)
                    <li class="flex justify-between px-6 py-3 text-sm">
                        <span>{{ $produit->nom }}</span>
                        <span class="font-medium text-amber-800">{{ (float) $produit->quantite }} {{ $produit->unite }} (seuil {{ (float) $produit->seuil_alerte }})</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.app>
