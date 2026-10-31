<x-layouts.app title="Caisse">
    <x-page-header title="Caisse" subtitle="Encaissements par période et mode de paiement." />
    @include('paiements.partials.onglets')

    <form method="GET" class="carte carte-corps mb-5 grid gap-4 sm:grid-cols-4 sm:items-end">
        <x-field name="du" type="date" label="Du" :value="$du->toDateString()" />
        <x-field name="au" type="date" label="Au" :value="$au->toDateString()" />
        <x-select name="mode" label="Mode de paiement" :value="request('mode')" :options="$modes->pluck('libelle', 'id')" placeholder="Tous" />
        <button class="btn-primaire">Filtrer</button>
    </form>

    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-stat label="Total encaissé" :value="\App\Support\Money::fcfa($totaux->sum())" icon="coins" />
        @foreach ($modes as $mode)
            @if ($totaux->has($mode->id))
                <x-stat :label="$mode->libelle" :value="\App\Support\Money::fcfa($totaux[$mode->id])" />
            @endif
        @endforeach
    </div>

    <div class="carte overflow-x-auto">
        @if ($paiements->isEmpty())
            <x-empty icon="wallet" title="Aucun paiement sur cette période" />
        @else
            <table class="tableau">
                <thead><tr><th>Date</th><th>Client</th><th>Mode</th><th class="hidden md:table-cell">Référence</th><th class="text-right">Montant</th><th class="hidden text-right md:table-cell">Reste</th><th></th></tr></thead>
                <tbody>
                    @foreach ($paiements as $paiement)
                        <tr @class(['opacity-50' => $paiement->estAnnule()])>
                            <td class="whitespace-nowrap">{{ $paiement->paye_le->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('clients.show', $paiement->client) }}" class="hover:underline">{{ $paiement->client->nom_complet }}</a></td>
                            <td>{{ $paiement->mode->libelle }}</td>
                            <td class="hidden text-gris-doux md:table-cell">{{ $paiement->reference ?? '—' }}</td>
                            <td class="text-right font-medium whitespace-nowrap {{ $paiement->estAnnule() ? 'line-through' : '' }}">@fcfa($paiement->montant_paye)</td>
                            <td class="hidden text-right text-gris-doux md:table-cell">@fcfa($paiement->reste_a_payer)</td>
                            <td class="text-right"><a href="{{ route('paiements.recu', $paiement) }}" class="inline-flex rounded-lg p-2 text-gris-doux hover:bg-beige-100 hover:text-bordeaux-700" title="Reçu"><x-icon name="receipt" class="size-4" /></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $paiements->links() }}
        @endif
    </div>
</x-layouts.app>
