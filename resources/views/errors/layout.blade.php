<x-layouts.guest>
    <div class="flex min-h-screen items-center justify-center px-6">
        <div class="max-w-md text-center">
            <p class="font-display text-7xl font-semibold text-bordeaux-700">@yield('code')</p>
            <h1 class="mt-4 text-xl font-semibold text-encre">@yield('titre')</h1>
            <p class="mt-2 text-sm text-gris-doux">@yield('message')</p>
            <a href="{{ url('/') }}" class="btn-primaire mt-8">Retour à l'accueil</a>
        </div>
    </div>
</x-layouts.guest>
