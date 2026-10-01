<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/logo-bagoes.jpg') }}" type="image/jpeg">
    <title>{{ $title ?? 'Admin - '.config('app.name', 'PS Rental') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex">
    @php
        $menu = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard'],
            ['label' => 'Billing', 'route' => 'admin.billing.create', 'pattern' => 'admin.billing.*'],
            ['label' => 'Active Rental', 'route' => 'admin.rentals.index', 'pattern' => 'admin.rentals.*'],
            ['label' => 'Bookings', 'route' => 'admin.bookings.index', 'pattern' => 'admin.bookings.*'],
            ['label' => 'Units', 'route' => 'admin.units.index', 'pattern' => 'admin.units.*'],
            ['label' => 'Games', 'route' => 'admin.games.index', 'pattern' => 'admin.games.*'],
            ['label' => 'Kategori', 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*'],
            ['label' => 'Price List', 'route' => 'admin.price-lists.index', 'pattern' => 'admin.price-lists.*'],
            ['label' => 'Carousel', 'route' => 'admin.carousels.index', 'pattern' => 'admin.carousels.*'],
            ['label' => 'Tasmota', 'route' => 'admin.tasmota.index', 'pattern' => 'admin.tasmota.*'],
            ['label' => 'Users', 'route' => 'admin.users.index', 'pattern' => 'admin.users.*'],
            ['label' => 'Settings', 'route' => 'admin.settings.edit', 'pattern' => 'admin.settings.*'],
        ];
    @endphp

    {{-- Layar kecil: sidebar jadi drawer yang menutupi konten; layar lg ke atas tetap menempel di kiri. --}}
    <div data-sidebar-overlay class="fixed inset-0 z-30 bg-black/60 lg:hidden hidden"></div>

    <aside
        data-sidebar
        class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 border-r border-slate-800 bg-slate-900 -translate-x-full transition-transform duration-200 flex flex-col
               lg:static lg:translate-x-0 lg:bg-slate-900/60 lg:sticky lg:top-0 lg:h-screen"
    >
        <div class="h-14 flex items-center gap-2 px-5 border-b border-slate-800 font-bold text-white tracking-tight">
            <img src="{{ asset('images/logo-bagoes.jpg') }}" alt="Bagoes Cafe" class="w-8 h-8 rounded-lg object-cover">
            <span>Bagoes <span class="text-brand-400">Admin</span></span>
            <button type="button" data-sidebar-close aria-label="Tutup menu" class="ml-auto lg:hidden text-slate-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">
            @foreach ($menu as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="block px-3 py-2 rounded-lg {{ request()->routeIs($item['pattern']) ? 'bg-brand-500/15 text-brand-300' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="p-3 border-t border-slate-800">
            @csrf
            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition text-sm">
                Logout
            </button>
        </form>
    </aside>

    <div class="flex-1 min-w-0">
        <header class="h-14 border-b border-slate-800 bg-slate-900/60 flex items-center gap-3 px-4 sm:px-6 sticky top-0 z-20">
            <button type="button" data-sidebar-toggle aria-label="Buka menu" class="lg:hidden -ml-1 p-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="font-semibold text-white truncate">{{ $title ?? 'Dashboard' }}</h1>
            <span class="ml-auto text-sm text-slate-400 truncate max-w-[40%] hidden sm:inline">{{ auth()->user()->name }}</span>
        </header>
        <main class="p-4 sm:p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg border border-rose-500/30 bg-rose-500/10 text-rose-400 text-sm px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
