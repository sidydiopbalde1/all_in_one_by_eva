@if (session('success'))
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
        <x-icon name="check" class="mt-0.5 size-4 shrink-0" />
        <span>{{ session('success') }}</span>
    </div>
@endif

@if ($errors->has('general') || $errors->has('whatsapp'))
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        <x-icon name="circle-alert" class="mt-0.5 size-4 shrink-0" />
        <span>{{ $errors->first('general') ?: $errors->first('whatsapp') }}</span>
    </div>
@endif
