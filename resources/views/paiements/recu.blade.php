<x-layouts.app title="Reçu">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <a href="{{ route('rendez-vous.show', $paiement->rendezVous) }}" class="inline-flex items-center gap-1 text-sm text-gris-doux hover:text-bordeaux-700"><x-icon name="arrow-left" class="size-4" /> Rendez-vous</a>
        <div class="flex gap-2">
            <button type="button" onclick="window.print()" class="btn-primaire"><x-icon name="printer" class="size-4" /> Imprimer</button>
        </div>
    </div>

    <article class="carte mx-auto max-w-md p-8 print:border-0 print:shadow-none">
        <header class="text-center">
            <p class="font-display text-3xl font-semibold text-bordeaux-700">All in One by Eva</p>
            <p class="text-xs tracking-[0.2em] text-gris-doux uppercase">Reçu de paiement</p>
            <p class="mt-3 text-sm">N° {{ str_pad($paiement->id, 6, '0', STR_PAD_LEFT) }} · {{ $paiement->paye_le->format('d/m/Y H:i') }}</p>
            @if ($paiement->estAnnule())
                <p class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">PAIEMENT ANNULÉ — {{ $paiement->motif_annulation }}</p>
            @endif
        </header>

        <dl class="mt-6 space-y-1 border-y border-dashed border-beige-300 py-4 text-sm">
            <div class="flex justify-between"><dt class="text-gris-doux">Client</dt><dd>{{ $paiement->client->nom_complet }}</dd></div>
            @if ($paiement->rendezVous)
                <div class="flex justify-between"><dt class="text-gris-doux">Prestation du</dt><dd>{{ $paiement->rendezVous->debut->format('d/m/Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gris-doux">Avec</dt><dd>{{ $paiement->rendezVous->employe->prenom }}</dd></div>
            @endif
        </dl>

        @if ($paiement->rendezVous)
            <ul class="space-y-1 py-4 text-sm">
                @foreach ($paiement->rendezVous->lignes as $ligne)
                    <li class="flex justify-between"><span>{{ $ligne->libelle }}</span><span>@fcfa($ligne->prix)</span></li>
                @endforeach
            </ul>
        @endif

        <dl class="space-y-1 border-t border-dashed border-beige-300 pt-4 text-sm">
            <div class="flex justify-between"><dt>Montant total</dt><dd>@fcfa($paiement->montant_total)</dd></div>
            <div class="flex justify-between text-base font-semibold"><dt>Payé ({{ $paiement->mode->libelle }})</dt><dd>@fcfa($paiement->montant_paye)</dd></div>
            <div class="flex justify-between"><dt>Reste à payer</dt><dd>@fcfa($paiement->reste_a_payer)</dd></div>
            @if ($paiement->reference)<div class="flex justify-between text-gris-doux"><dt>Référence</dt><dd>{{ $paiement->reference }}</dd></div>@endif
        </dl>

        <p class="mt-6 text-center text-xs text-gris-doux">Encaissé par {{ $paiement->auteur->name }} · Merci de votre visite !</p>
    </article>

    @can('paiements.annuler')
        @unless ($paiement->estAnnule())
            <details class="carte carte-corps mx-auto mt-6 max-w-md text-sm print:hidden">
                <summary class="cursor-pointer font-medium text-red-700">Annuler ce paiement</summary>
                <form method="POST" action="{{ route('paiements.annuler', $paiement) }}" class="mt-4 space-y-3" data-confirm="Confirmer l'annulation de ce paiement ?">
                    @csrf @method('PATCH')
                    <x-field name="motif" label="Motif de l'annulation" required help="Le paiement reste dans l'historique mais n'est plus compté dans les recettes." />
                    <button class="btn-danger"><x-icon name="ban" class="size-4" /> Annuler le paiement</button>
                </form>
            </details>
        @endunless
    @endcan
</x-layouts.app>
