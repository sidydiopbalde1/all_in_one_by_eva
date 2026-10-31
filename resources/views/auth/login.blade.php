<x-layouts.guest>
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-bordeaux-700 lg:flex lg:flex-col lg:justify-between lg:p-14">
            <div class="absolute -top-24 -right-24 size-96 rounded-full bg-bordeaux-600/40"></div>
            <div class="absolute -bottom-32 -left-16 size-[28rem] rounded-full bg-bordeaux-800/50"></div>
            <p class="relative text-xs tracking-[0.25em] text-beige-200/80 uppercase">Espace de gestion</p>
            <div class="relative">
                <h1 class="font-display text-6xl leading-tight font-semibold text-white">All in One<br>by Eva</h1>
                <p class="mt-5 max-w-sm text-beige-200/85">Beauté et bien-être pour toute la famille : clients, rendez-vous, caisse et fidélité réunis au même endroit.</p>
            </div>
            <p class="relative text-xs text-beige-200/60">Coiffure · Soins · Massages · Onglerie</p>
        </div>

        <div class="flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">
                <h1 class="font-display text-4xl font-semibold lg:hidden">All in One by Eva</h1>
                <h2 class="mt-6 text-2xl font-semibold text-encre lg:mt-0">Connexion</h2>
                <p class="mt-1 text-sm text-gris-doux">Accédez à l'espace de gestion du salon.</p>

                <form method="POST" action="{{ url('/connexion') }}" class="mt-8 space-y-5">
                    @csrf
                    <x-field name="email" type="email" label="Adresse e-mail" required autofocus autocomplete="username" />
                    <x-field name="password" type="password" label="Mot de passe" required autocomplete="current-password" />
                    <x-checkbox name="remember" label="Rester connecté(e)" />
                    <button type="submit" class="btn-primaire w-full py-3">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.guest>
