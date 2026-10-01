@props(['label', 'value'])
<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-800 bg-slate-900 p-4']) }}>
    <p class="text-xs text-slate-500 mb-1">{{ $label }}</p>
    <p class="text-xl font-semibold text-white" data-stat-value>{{ $value }}</p>
</div>
