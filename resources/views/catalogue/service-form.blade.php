@php($edition = $service->exists)
<x-layouts.app :title="$edition ? 'Modifier la prestation' : 'Nouvelle prestation'">
    <x-page-header :title="$edition ? $service->nom : 'Nouvelle prestation'" />

    <form method="POST" action="{{ $edition ? route('services.update', $service) : route('services.store') }}" class="carte carte-corps max-w-2xl space-y-5">
        @csrf
        @if ($edition) @method('PUT') @endif
        <x-field name="nom" label="Nom de la prestation" :value="$service->nom" required />
        <x-select name="categorie_service_id" label="Catégorie" :value="$service->categorie_service_id" :options="$categories->pluck('nom', 'id')" placeholder="Choisir…" required />
        <x-field name="description" type="textarea" label="Description" :value="$service->description" />
        <div class="grid gap-5 sm:grid-cols-3">
            <x-field name="prix" type="number" min="0" step="500" label="Prix (FCFA)" :value="$service->prix" required />
            <x-field name="duree_minutes" type="number" min="5" step="5" label="Durée (min)" :value="$service->duree_minutes" required />
            <x-select name="zone" label="Zone" :value="$service->zone?->value" :options="collect(\App\Enums\ZoneSalon::cases())->mapWithKeys(fn ($z) => [$z->value => $z->label()])" required />
        </div>
        <x-checkbox name="is_active" label="Prestation active" :checked="$service->is_active" help="Une prestation inactive n'est plus proposée à la réservation." />
        <div class="flex justify-end gap-3 border-t border-beige-200 pt-5">
            <a href="{{ route('catalogue') }}" class="btn-secondaire">Annuler</a>
            <button class="btn-primaire">Enregistrer</button>
        </div>
    </form>
</x-layouts.app>
