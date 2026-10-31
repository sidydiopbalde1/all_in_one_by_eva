<nav class="mb-5 flex w-fit gap-1 rounded-xl bg-beige-100/70 p-1">
    <a href="{{ route('paiements.index') }}" @class(['onglet', 'onglet-actif' => request()->routeIs('paiements.index')])>Encaissements</a>
    <a href="{{ route('paiements.impayes') }}" @class(['onglet', 'onglet-actif' => request()->routeIs('paiements.impayes')])>Impayés</a>
</nav>
