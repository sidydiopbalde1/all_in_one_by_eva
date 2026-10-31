<div class="flex items-center justify-between gap-3">
    <div class="min-w-0">
        <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
        <p class="text-xs text-beige-200/70">{{ auth()->user()->role->label }}</p>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="rounded-lg p-2 text-beige-100/80 hover:bg-white/10 hover:text-white" title="Se déconnecter" aria-label="Se déconnecter">
            <x-icon name="log-out" class="size-[18px]" />
        </button>
    </form>
</div>
