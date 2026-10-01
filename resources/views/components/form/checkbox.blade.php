@props(['name', 'label', 'checked' => false])
<label class="flex items-center gap-2 text-sm text-slate-300">
    <input
        type="checkbox" name="{{ $name }}" value="1"
        {{ old($name, $checked) ? 'checked' : '' }}
        class="rounded border-slate-700 bg-slate-800 text-brand-500 focus:ring-brand-500"
    >
    {{ $label }}
</label>
@error($name)
    <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
@enderror
