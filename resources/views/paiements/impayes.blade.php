<x-layouts.app title="Impayés">
    <x-page-header title="Caisse" subtitle="Rendez-vous réalisés ou en cours avec un reste à payer." />
    @include('paiements.partials.onglets')

    <div class="carte overflow-x-auto">
        @if ($rendezVous->isEmpty())
            <x-empty icon="check" title="Aucun impayé" text="Tous les rendez-vous réalisés sont réglés." />
        @else
            <table class="tableau">
                <thead><tr><th>Date</th><th>Client</th><th class="text-right">Prévu</th><th class="text-right">Payé</th><th class="text-right">Reste</th><th></th></tr></thead>
                <tbody>
                    @foreach ($rendezVous as $rdv)
                        <tr>
                            <td>{{ $rdv->debut->format('d/m/Y') }}</td>
                            <td><a href="{{ route('rendez-vous.show', $rdv) }}" class="hover:underline">{{ $rdv->client->nom_complet }}</a></td>
                            <td class="text-right">@fcfa($rdv->prix_prevu)</td>
                            <td class="text-right">@fcfa($rdv->deja_paye ?? 0)</td>
                            <td class="text-right font-semibold text-red-700">@fcfa($rdv->prix_prevu - (int) $rdv->deja_paye)</td>
                            <td class="text-right"><a href="{{ route('paiements.create', $rdv) }}" class="btn-primaire btn-sm">Encaisser</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.app>
