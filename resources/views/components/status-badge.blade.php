@props(['status' => 'AVAILABLE'])
@php
    $styles = [
        'AVAILABLE' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
        'BOOKED' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
        'PLAYING' => 'bg-brand-500/15 text-brand-400 border-brand-500/30',
        'MAINTENANCE' => 'bg-orange-500/15 text-orange-400 border-orange-500/30',
        'OFFLINE' => 'bg-slate-500/15 text-slate-400 border-slate-500/30',
        'PENDING' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
        'CONFIRMED' => 'bg-brand-500/15 text-brand-400 border-brand-500/30',
        'CANCELLED' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
        'COMPLETED' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
        'ACTIVE' => 'bg-brand-500/15 text-brand-400 border-brand-500/30',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border '.($styles[$status] ?? $styles['OFFLINE'])]) }}>
    {{ $status }}
</span>
