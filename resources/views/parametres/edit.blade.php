<x-layouts.app title="Paramètres">
    <x-page-header title="Paramètres du salon" subtitle="Règles de gestion et modèles de messages WhatsApp." />

    <form method="POST" action="{{ route('parametres.update') }}" class="grid gap-6 lg:grid-cols-3">
        @csrf @method('PUT')
        <section class="carte carte-corps space-y-5">
            <h2 class="font-display text-xl font-semibold">Règles</h2>
            <x-field name="pause_minutes" type="number" min="0" label="Pause entre rendez-vous (min)" :value="$parametres['pause_minutes']" required />
            <x-field name="fidelite_fcfa_par_point" type="number" min="1" label="FCFA dépensés pour 1 point" :value="$parametres['fidelite_fcfa_par_point']" required />
            <x-field name="inactivite_semaines" type="number" min="1" label="Client à relancer après (semaines)" :value="$parametres['inactivite_semaines']" required />
            <x-field name="offre_anniversaire" label="Offre d'anniversaire" :value="$parametres['offre_anniversaire']" placeholder="Ex. soin du visage offert" />
        </section>

        <section class="carte carte-corps space-y-5 lg:col-span-2">
            <h2 class="font-display text-xl font-semibold">Modèles de messages</h2>
            <p class="aide">Variables disponibles : [Prénom], [date], [heure], [prestation].</p>
            @foreach ($types as $type)
                <x-field :name="'modeles['.$type->value.']'" type="textarea" :label="$type->label()" :value="$modeles[$type->value]->contenu ?? ''" required />
            @endforeach
            <div class="flex justify-end border-t border-beige-200 pt-5">
                <button class="btn-primaire">Enregistrer les paramètres</button>
            </div>
        </section>
    </form>
</x-layouts.app>
