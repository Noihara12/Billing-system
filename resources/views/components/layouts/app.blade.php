<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/logo-bagoes.jpg') }}" type="image/jpeg">
    <title>{{ $title ?? config('app.name', 'PS Rental') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <nav class="border-b border-slate-800 bg-slate-900/60 backdrop-blur sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            @php
                $menu = [
                    ['label' => 'Beranda', 'route' => 'dashboard', 'pattern' => 'dashboard'],
                    ['label' => 'Booking', 'route' => 'booking.create', 'pattern' => 'booking.create'],
                    ['label' => 'Tentang Kami', 'route' => 'about', 'pattern' => 'about'],
                ];

                $menu = auth()->check()
                    ? [...$menu,
                        ['label' => 'My Booking', 'route' => 'booking.index', 'pattern' => 'booking.index|booking.show'],
                        ['label' => 'Profile', 'route' => 'profile.edit', 'pattern' => 'profile.*'],
                    ]
                    : [...$menu, ['label' => 'Cek Booking', 'route' => 'booking.lookup', 'pattern' => 'booking.lookup*']];
            @endphp

            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-white tracking-tight">
                <img src="{{ asset('images/logo-bagoes.jpg') }}" alt="Bagoes Cafe" class="w-9 h-9 rounded-lg object-cover">
                <span>Bagoes <span class="text-brand-400">Cafe</span></span>
            </a>

            <div class="hidden sm:flex items-center gap-6 text-sm">
                @foreach ($menu as $item)
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs(explode('|', $item['pattern'])) ? 'text-brand-400 font-medium' : 'text-slate-300 hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="flex items-center gap-3 text-sm">
                @auth
                    <span class="text-slate-400 hidden sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 transition">Masuk</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline px-3 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white transition">Daftar</a>
                @endauth

                {{-- Layar kecil: seluruh menu dipindah ke panel yang dibuka lewat ikon ini. --}}
                <button type="button" data-navbar-toggle aria-label="Buka menu" aria-expanded="false" class="sm:hidden -mr-1 p-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <div data-navbar-panel class="sm:hidden hidden border-t border-slate-800 bg-slate-900">
            <div class="px-4 py-3 space-y-1 text-sm">
                @foreach ($menu as $item)
                    <a href="{{ route($item['route']) }}" class="block px-3 py-2.5 rounded-lg {{ request()->routeIs(explode('|', $item['pattern'])) ? 'bg-brand-500/15 text-brand-300 font-medium' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <div class="pt-2 mt-2 border-t border-slate-800">
                    @auth
                        <p class="px-3 pb-2 text-xs text-slate-500">Masuk sebagai {{ auth()->user()->name }}</p>
                        <form method="POST" action="{{ route('logout') }}" class="px-3">
                            @csrf
                            <button type="submit" class="w-full rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 py-2.5 transition">Logout</button>
                        </form>
                    @else
                        <div class="px-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('login') }}" class="text-center rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 py-2.5 transition">Masuk</a>
                            <a href="{{ route('register') }}" class="text-center rounded-lg bg-brand-600 hover:bg-brand-500 text-white py-2.5 transition">Daftar</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        {{ $slot }}
    </main>
</body>
</html>
