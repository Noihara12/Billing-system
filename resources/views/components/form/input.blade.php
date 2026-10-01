@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false])
<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-300 mb-1.5">
        {{ $label }} @if ($required)<span class="text-rose-400">*</span>@endif
    </label>
    <input
        id="{{ $name }}" type="{{ $type }}" name="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500']) }}
    >
    @error($name)
        <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
    @enderror
</div>
