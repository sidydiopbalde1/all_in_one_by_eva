@php($gerer = auth()->user()->can('rdv.gerer'))
<x-layouts.app title="Rendez-vous">
    <a href="{{ $gerer ? route('rendez-vous.index', ['date' => $rdv->debut->toDateString()]) : route('planning') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gris-doux hover:text-bordeaux-700"><x-icon name="arrow-left" class="size-4" /> Calendrier</a>

    <x-page-header :title="$rdv->client->nom_complet" :subtitle="ucfirst($rdv->debut->locale('fr')->translatedFormat('l j F Y')).' · '.$rdv->debut->format('H:i').'–'.$rdv->fin->format('H:i')">
        <x-slot:actions>
            <x-statut-rdv :statut="$rdv->statut" />
            @can('whatsapp.envoyer')
                <a href="{{ route('whatsapp.rappel', $rdv) }}" target="_blank" rel="noopener" class="btn-whatsapp"><x-icon name="message-circle" class="size-4" /> Rappel WhatsApp</a>
            @endcan
            @if ($gerer && ! in_array($rdv->statut, [\App\Enums\StatutRendezVous::Termine, \App\Enums\StatutRendezVous::Annule], true))
                <a href="{{ route('rendez-vous.edit', $rdv) }}" class="btn-secondaire"><x-icon name="pencil" class="size-4" /> Déplacer / modifier</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="carte">
                <h2 class="border-b border-beige-200 px-6 py-4 font-display text-xl font-semibold">Prestations</h2>
                @foreach ($rdv->lignes as $ligne)
                    <div class="flex justify-between border-b border-beige-200/70 px-6 py-3 text-sm last:border-0">
                        <span>{{ $ligne->libelle }} <span class="text-gris-doux">· {{ $ligne->duree_minutes }} min</span></span>
                        <span class="font-medium">@fcfa($ligne->prix)</span>
                    </div>
                @endforeach
                <div class="flex justify-between bg-beige-100/50 px-6 py-3 text-sm font-semibold">
                    <span>Prix prévu</span><span>@fcfa($rdv->prix_prevu)</span>
                </div>
            </section>

            @can('paiements.enregistrer')
                <section class="carte">
                    <div class="flex items-center justify-between border-b border-beige-200 px-6 py-4">
                        <h2 class="font-display text-xl font-semibold">Paiements</h2>
                        @if ($rdv->resteAPayer() > 0 && ! $rdv->statut->libereLeCreneau())
                            <a href="{{ route('paiements.create', $rdv) }}" class="btn-primaire btn-sm"><x-icon name="wallet" class="size-3.5" /> Encaisser @fcfa($rdv->resteAPayer())</a>
                        @elseif ($rdv->estSolde())
                            <x-badge tone="emerald">Soldé</x-badge>
                        @endif
                    </div>
                    @forelse ($rdv->paiements as $paiement)
                        <div @class(['flex items-center justify-between border-b border-beige-200/70 px-6 py-3 text-sm last:border-0', 'opacity-50' => $paiement->estAnnule()])>
                            <span>{{ $paiement->paye_le->format('d/m/Y H:i') }} · {{ $paiement->mode->libelle }} @if ($paiement->estAnnule())<x-badge tone="red">Annulé</x-badge>@endif</span>
                            <a href="{{ route('paiements.recu', $paiement) }}" class="font-medium hover:underline">@fcfa($paiement->montant_paye)</a>
                        </div>
                    @empty
                        <p class="px-6 py-4 text-sm text-gris-doux">Aucun paiement enregistré.</p>
                    @endforelse
                </section>
            @endcan
        </div>

        <aside class="space-y-6">
            <section class="carte carte-corps space-y-2 text-sm">
                <p><span class="text-gris-doux">Professionnel(le) :</span> {{ $rdv->employe->nom_complet }}</p>
                <p><span class="text-gris-doux">Zone :</span> {{ $rdv->zone->label() }}</p>
                <p><span class="text-gris-doux">Téléphone client :</span> @tel($rdv->client->telephone)</p>
                @if ($rdv->commentaire)<p class="rounded-lg bg-beige-100/60 p-3">{{ $rdv->commentaire }}</p>@endif
                <p class="pt-2 text-xs text-gris-doux">Créé par {{ $rdv->auteur->name }} le {{ $rdv->created_at->format('d/m/Y à H:i') }}</p>
            </section>

            <section class="carte carte-corps">
                <h2 class="font-display text-xl font-semibold">Changer le statut</h2>
                <form method="POST" action="{{ route('rendez-vous.statut', $rdv) }}" class="mt-4 grid grid-cols-2 gap-2">
                    @csrf @method('PATCH')
                    @foreach ($statuts as $statut)
                        <button name="statut" value="{{ $statut->value }}" @disabled($rdv->statut === $statut)
                                @class(['btn btn-sm border', 'border-bordeaux-700 bg-bordeaux-700 text-white' => $rdv->statut === $statut, 'border-beige-300 bg-white hover:bg-beige-100' => $rdv->statut !== $statut])>
                            {{ $statut->label() }}
                        </button>
                    @endforeach
                </form>
            </section>
        </aside>
    </div>
</x-layouts.app>
