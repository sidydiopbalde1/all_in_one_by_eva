@php($edition = $employe->exists)
@php($selection = collect(old('services', $employe->services?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id))
@php($horaires = $employe->disponibilites?->keyBy('jour_semaine') ?? collect())
<x-layouts.app :title="$edition ? 'Modifier un membre' : 'Nouveau membre'">
    <x-page-header :title="$edition ? $employe->nom_complet : 'Nouveau membre de l\'équipe'" />

    <form method="POST" action="{{ $edition ? route('employes.update', $employe) : route('employes.store') }}" class="grid gap-6 lg:grid-cols-2">
        @csrf
        @if ($edition) @method('PUT') @endif

        <section class="carte carte-corps space-y-5">
            <h2 class="font-display text-xl font-semibold">Informations</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="prenom" label="Prénom" :value="$employe->prenom" required />
                <x-field name="nom" label="Nom" :value="$employe->nom" required />
                <x-field name="telephone" type="tel" label="Téléphone" :value="$employe->telephone" required />
                <x-field name="fonction" label="Fonction" :value="$employe->fonction" required placeholder="Coiffeuse, masseur…" />
            </div>
            <x-field name="specialites" label="Spécialités" :value="$employe->specialites" placeholder="Tresses, coloration…" />
            <x-field name="taux_commission" type="number" step="0.5" min="0" max="100" label="Commission (%)" :value="$employe->taux_commission" help="Visible uniquement par les personnes autorisées." />
            <x-checkbox name="is_active" label="Membre actif" :checked="$employe->is_active" help="Un membre inactif ne peut plus recevoir de rendez-vous." />
        </section>

        <div class="space-y-6">
            <section class="carte carte-corps">
                <h2 class="font-display text-xl font-semibold">Horaires de travail</h2>
                <div class="mt-4 space-y-2">
                    @foreach (\App\Models\Disponibilite::JOURS as $jour => $libelle)
                        @php($h = $horaires->get($jour))
                        <div class="grid grid-cols-[7rem_1fr_1fr] items-center gap-3">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="hidden" name="horaires[{{ $jour }}][actif]" value="0">
                                <input type="checkbox" name="horaires[{{ $jour }}][actif]" value="1" @checked(old("horaires.$jour.actif", (bool) $h)) class="size-4 rounded border-beige-300 text-bordeaux-700">
                                {{ $libelle }}
                            </label>
                            <input type="time" name="horaires[{{ $jour }}][debut]" value="{{ old("horaires.$jour.debut", $h ? substr($h->heure_debut, 0, 5) : '09:00') }}" class="champ py-2" aria-label="Début {{ $libelle }}">
                            <input type="time" name="horaires[{{ $jour }}][fin]" value="{{ old("horaires.$jour.fin", $h ? substr($h->heure_fin, 0, 5) : '19:00') }}" class="champ py-2" aria-label="Fin {{ $libelle }}">
                        </div>
                        @error("horaires.$jour.fin")<p class="erreur">{{ $message }}</p>@enderror
                    @endforeach
                </div>
            </section>

            <section class="carte carte-corps">
                <h2 class="font-display text-xl font-semibold">Prestations proposées</h2>
                <div class="mt-4 max-h-80 space-y-4 overflow-y-auto">
                    @foreach ($services as $categorie => $liste)
                        <div>
                            <p class="mb-1 text-xs font-semibold tracking-wide text-gris-doux uppercase">{{ $categorie }}</p>
                            @foreach ($liste as $service)
                                <label class="flex items-center gap-3 rounded-lg px-2 py-1 text-sm hover:bg-beige-100/60">
                                    <input type="checkbox" name="services[]" value="{{ $service->id }}" @checked($selection->contains($service->id)) class="size-4 rounded border-beige-300 text-bordeaux-700">
                                    {{ $service->nom }}
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="flex justify-end gap-3">
                <a href="{{ route('employes.index') }}" class="btn-secondaire">Annuler</a>
                <button class="btn-primaire">Enregistrer</button>
            </div>
        </div>
    </form>
</x-layouts.app>
