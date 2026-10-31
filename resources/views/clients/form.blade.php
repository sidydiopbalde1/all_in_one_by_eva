@php($edition = $client->exists)
<x-layouts.app :title="$edition ? 'Modifier le client' : 'Nouveau client'">
    <x-page-header :title="$edition ? $client->nom_complet : 'Nouveau client'" :subtitle="$edition ? 'Modifier la fiche client' : 'Le numéro de téléphone sert à détecter les doublons.'" />

    <form method="POST" action="{{ $edition ? route('clients.update', $client) : route('clients.store') }}" class="carte carte-corps max-w-3xl space-y-6">
        @csrf
        @if ($edition) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="prenom" label="Prénom" :value="$client->prenom" required />
            <x-field name="nom" label="Nom" :value="$client->nom" required />
            <x-field name="telephone" type="tel" label="Téléphone" :value="$client->telephone ? \App\Support\Telephone::format($client->telephone) : null" required help="Ex. 77 123 45 67 — l'indicatif +221 est ajouté automatiquement." />
            <x-field name="whatsapp" type="tel" label="WhatsApp (si différent)" :value="$client->whatsapp ? \App\Support\Telephone::format($client->whatsapp) : null" />
            <x-field name="email" type="email" label="E-mail" :value="$client->email" />
            <x-field name="date_naissance" type="date" label="Date de naissance" :value="$client->date_naissance?->toDateString()" help="Facultative — utilisée pour les vœux d'anniversaire." />
            <x-select name="preference_communication" label="Préférence de communication" :value="$client->preference_communication"
                      :options="['whatsapp' => 'WhatsApp', 'appel' => 'Appel', 'sms' => 'SMS', 'email' => 'E-mail']" required />
            @if ($edition)
                <x-select name="statut" label="Statut" :value="$client->statut->value"
                          :options="collect(\App\Enums\StatutClient::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" />
            @endif
        </div>

        <x-field name="preferences_prestations" label="Préférences de prestations" :value="$client->preferences_prestations" placeholder="Ex. tresses fines, massage relaxant…" />
        <x-field name="notes" type="textarea" label="Notes utiles" :value="$client->notes" />

        <div class="rounded-xl bg-beige-100/60 p-4">
            <x-checkbox name="consentement_promo" :checked="$client->consentement_promo"
                        label="Accepte de recevoir les offres et promotions"
                        help="Le client peut retirer son consentement à tout moment. La date de modification est enregistrée." />
        </div>

        <div class="flex justify-end gap-3 border-t border-beige-200 pt-5">
            <a href="{{ $edition ? route('clients.show', $client) : route('clients.index') }}" class="btn-secondaire">Annuler</a>
            <button type="submit" class="btn-primaire">{{ $edition ? 'Enregistrer' : 'Créer le client' }}</button>
        </div>
    </form>
</x-layouts.app>
