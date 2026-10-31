@php
    $edition = $rdv->exists;
    $servicesChoisis = collect(old('services', $rdv->lignes?->pluck('service_id')->filter()->all() ?? []))->map(fn ($id) => (int) $id);
    $forfaitsChoisis = collect(old('forfaits', $rdv->lignes?->pluck('forfait_id')->filter()->all() ?? []))->map(fn ($id) => (int) $id);
@endphp
<x-layouts.app :title="$edition ? 'Modifier le rendez-vous' : 'Nouveau rendez-vous'">
    <x-page-header :title="$edition ? 'Modifier le rendez-vous' : 'Nouveau rendez-vous'" subtitle="L'heure de fin est calculée à partir des durées des prestations." />

    <form method="POST" action="{{ $edition ? route('rendez-vous.update', $rdv) : route('rendez-vous.store') }}" class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($edition) @method('PUT') @endif

        <div class="space-y-6 lg:col-span-2">
            <section class="carte carte-corps space-y-5">
                <h2 class="font-display text-xl font-semibold">Client & professionnel</h2>
                <div>
                    <label for="client_id" class="label">Client <span class="text-bordeaux-600">*</span></label>
                    <input type="search" class="champ mb-2" placeholder="Filtrer par nom ou numéro…" data-filter-target="client_id" aria-label="Filtrer les clients">
                    <select id="client_id" name="client_id" required size="5" @class(['champ', 'champ-erreur' => $errors->has('client_id')])>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}" @selected((int) old('client_id', $rdv->client_id) === $c->id)>{{ $c->nom_complet }} · @tel($c->telephone)</option>
                        @endforeach
                    </select>
                    @error('client_id')<p class="erreur">{{ $message }}</p>@enderror
                    <p class="aide">Client introuvable ? <a href="{{ route('clients.create') }}" class="text-bordeaux-700 underline">Créer une fiche</a></p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-select name="employe_id" label="Professionnel(le)" :value="$rdv->employe_id" :options="$employes->pluck('nom_complet', 'id')" placeholder="Choisir…" required />
                    <x-select name="zone" label="Zone du salon" :value="$rdv->zone?->value" :options="collect($zones)->mapWithKeys(fn ($z) => [$z->value => $z->label()])" required />
                    <x-field name="date" type="date" label="Date" :value="$date" required />
                    <x-field name="heure_debut" type="time" step="900" label="Heure de début" :value="$rdv->debut?->format('H:i')" required />
                </div>
            </section>

            <section class="carte carte-corps">
                <h2 class="font-display text-xl font-semibold">Prestations</h2>
                @error('services')<p class="erreur">{{ $message }}</p>@enderror
                @error('zone')<p class="erreur">{{ $message }}</p>@enderror
                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    @foreach ($services as $categorie => $liste)
                        <fieldset>
                            <legend class="mb-1 text-xs font-semibold tracking-wide text-gris-doux uppercase">{{ $categorie }}</legend>
                            @foreach ($liste as $service)
                                <label class="flex items-center gap-3 rounded-lg px-2 py-1.5 text-sm hover:bg-beige-100/60">
                                    <input type="checkbox" name="services[]" value="{{ $service->id }}" data-duree="{{ $service->duree_minutes }}" data-prix="{{ $service->prix }}"
                                           @checked($servicesChoisis->contains($service->id)) class="size-4 rounded border-beige-300 text-bordeaux-700">
                                    <span class="flex-1">{{ $service->nom }} <span class="text-xs text-gris-doux">· {{ $service->duree_minutes }} min</span></span>
                                    <span class="text-xs text-gris-doux">@fcfa($service->prix)</span>
                                </label>
                            @endforeach
                        </fieldset>
                    @endforeach
                </div>
                @if ($forfaits->isNotEmpty())
                    <fieldset class="mt-6 border-t border-beige-200 pt-4">
                        <legend class="mb-1 pt-4 text-xs font-semibold tracking-wide text-gris-doux uppercase">Forfaits</legend>
                        @foreach ($forfaits as $forfait)
                            <label class="flex items-center gap-3 rounded-lg px-2 py-1.5 text-sm hover:bg-beige-100/60">
                                <input type="checkbox" name="forfaits[]" value="{{ $forfait->id }}" data-duree="{{ $forfait->dureeTotale() }}" data-prix="{{ $forfait->prix }}"
                                       @checked($forfaitsChoisis->contains($forfait->id)) class="size-4 rounded border-beige-300 text-bordeaux-700">
                                <span class="flex-1">{{ $forfait->nom }} <span class="text-xs text-gris-doux">· {{ $forfait->services->pluck('nom')->join(' + ') }}</span></span>
                                <span class="text-xs text-gris-doux">@fcfa($forfait->prix)</span>
                            </label>
                        @endforeach
                    </fieldset>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section id="recap-rdv" class="carte carte-corps lg:sticky lg:top-8">
                <h2 class="font-display text-xl font-semibold">Récapitulatif</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gris-doux">Durée des prestations</dt><dd class="font-semibold" data-recap-duree>0h00</dd></div>
                    <div class="flex justify-between"><dt class="text-gris-doux">Prix prévu</dt><dd class="font-semibold" data-recap-prix>0 FCFA</dd></div>
                </dl>
                <p class="aide mt-3">Le créneau est vérifié à l'enregistrement : horaires du professionnel et absence de chevauchement.</p>
                @error('heure_debut')<p class="erreur mt-3 rounded-lg bg-red-50 p-2">{{ $message }}</p>@enderror
                @error('employe_id')<p class="erreur mt-3 rounded-lg bg-red-50 p-2">{{ $message }}</p>@enderror
                <x-field name="commentaire" type="textarea" label="Commentaire" :value="$rdv->commentaire" class="mt-5" />
                <button class="btn-primaire mt-5 w-full">{{ $edition ? 'Enregistrer les modifications' : 'Créer le rendez-vous' }}</button>
            </section>
        </aside>
    </form>
</x-layouts.app>
