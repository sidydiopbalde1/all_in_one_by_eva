@php($edition = $forfait->exists)
@php($selection = collect(old('services', $forfait->services?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id))
<x-layouts.app :title="$edition ? 'Modifier le forfait' : 'Nouveau forfait'">
    <x-page-header :title="$edition ? $forfait->nom : 'Nouveau forfait'" subtitle="Regroupez plusieurs prestations avec un prix spécifique." />

    <form method="POST" action="{{ $edition ? route('forfaits.update', $forfait) : route('forfaits.store') }}" class="carte carte-corps max-w-2xl space-y-5">
        @csrf
        @if ($edition) @method('PUT') @endif
        <x-field name="nom" label="Nom du forfait" :value="$forfait->nom" required />
        <x-field name="description" type="textarea" label="Description" :value="$forfait->description" />
        <x-field name="prix" type="number" min="0" step="500" label="Prix du forfait (FCFA)" :value="$forfait->prix" required />

        <fieldset>
            <legend class="label">Prestations incluses <span class="text-bordeaux-600">*</span></legend>
            <div class="max-h-72 space-y-1 overflow-y-auto rounded-xl border border-beige-300 p-3">
                @foreach ($services as $service)
                    <label class="flex items-center gap-3 rounded-lg px-2 py-1.5 text-sm hover:bg-beige-100/60">
                        <input type="checkbox" name="services[]" value="{{ $service->id }}" @checked($selection->contains($service->id)) class="size-4 rounded border-beige-300 text-bordeaux-700">
                        <span class="flex-1">{{ $service->nom }} <span class="text-xs text-gris-doux">· {{ $service->categorie->nom }}</span></span>
                        <span class="text-xs text-gris-doux">@fcfa($service->prix)</span>
                    </label>
                @endforeach
            </div>
            @error('services')<p class="erreur">{{ $message }}</p>@enderror
        </fieldset>

        <x-checkbox name="is_active" label="Forfait actif" :checked="$forfait->is_active" />
        <div class="flex justify-end gap-3 border-t border-beige-200 pt-5">
            <a href="{{ route('catalogue') }}" class="btn-secondaire">Annuler</a>
            <button class="btn-primaire">Enregistrer</button>
        </div>
    </form>
</x-layouts.app>
