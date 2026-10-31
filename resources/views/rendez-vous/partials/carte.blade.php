@php($compact = $compact ?? false)
<a href="{{ route('rendez-vous.show', $rdv) }}" @class([
    'block rounded-xl border-l-4 bg-beige-100/50 px-3 py-2 hover:bg-beige-100',
    'border-bordeaux-700' => ! $rdv->statut->libereLeCreneau(),
    'border-stone-300 opacity-60' => $rdv->statut->libereLeCreneau(),
])>
    <span class="flex items-center justify-between gap-2 text-xs">
        <span class="font-semibold text-bordeaux-700">{{ $rdv->debut->format('H:i') }}–{{ $rdv->fin->format('H:i') }}</span>
        @unless ($compact)<x-statut-rdv :statut="$rdv->statut" />@endunless
    </span>
    <span class="mt-0.5 block truncate text-sm font-medium">{{ $rdv->client->nom_complet }}</span>
    <span class="block truncate text-xs text-gris-doux">{{ $rdv->libellePrestations() }}@if ($compact) · {{ $rdv->employe->prenom }}@endif</span>
</a>
