<x-layouts.app title="Encaisser">
    <x-page-header title="Encaisser un paiement" :subtitle="$rdv->client->nom_complet.' · '.$rdv->debut->format('d/m/Y H:i')" />

    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('paiements.store', $rdv) }}" class="carte carte-corps space-y-5 lg:col-span-2">
            @csrf
            <div class="grid grid-cols-3 gap-4 rounded-xl bg-beige-100/60 p-4 text-sm">
                <div><p class="text-gris-doux">Montant total</p><p class="text-lg font-semibold">@fcfa($rdv->prix_prevu)</p></div>
                <div><p class="text-gris-doux">Déjà payé</p><p class="text-lg font-semibold">@fcfa($rdv->prix_prevu - $reste)</p></div>
                <div><p class="text-gris-doux">Reste à payer</p><p class="text-lg font-semibold text-bordeaux-700">@fcfa($reste)</p></div>
            </div>

            @if ($reste === 0)
                <x-empty icon="check" title="Ce rendez-vous est entièrement réglé." />
            @else
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="montant" type="number" min="1" :max="$reste" label="Montant encaissé (FCFA)" :value="$reste" required help="Saisissez un montant inférieur pour un paiement partiel." />
                    <x-select name="mode_paiement_id" label="Mode de paiement" :options="$modes->pluck('libelle', 'id')" placeholder="Choisir…" required />
                </div>
                <x-field name="reference" label="Référence (facultative)" placeholder="N° de transaction Wave, Orange Money…" />
                <div class="flex justify-end gap-3 border-t border-beige-200 pt-5">
                    <a href="{{ route('rendez-vous.show', $rdv) }}" class="btn-secondaire">Annuler</a>
                    <button class="btn-primaire"><x-icon name="check" class="size-4" /> Enregistrer le paiement</button>
                </div>
            @endif
        </form>

        <aside class="carte carte-corps text-sm">
            <h2 class="font-display text-xl font-semibold">Prestations</h2>
            <ul class="mt-3 space-y-2">
                @foreach ($rdv->lignes as $ligne)
                    <li class="flex justify-between"><span>{{ $ligne->libelle }}</span><span>@fcfa($ligne->prix)</span></li>
                @endforeach
            </ul>
        </aside>
    </div>
</x-layouts.app>
