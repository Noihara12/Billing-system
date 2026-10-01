<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/logo-bagoes.jpg') }}" type="image/jpeg">
    <title>{{ $title ?? config('app.name', 'PS Rental') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo-bagoes.jpg') }}" alt="Bagoes Cafe" class="w-24 h-24 mx-auto mb-3 rounded-2xl object-cover ring-1 ring-brand-500/30">
            <span class="inline-flex items-center gap-2 text-2xl font-bold tracking-tight text-white">
                Bagoes <span class="text-brand-400">Cafe</span>
            </span>
            <p class="text-slate-400 text-sm mt-1">Sistem rental &amp; billing PlayStation</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
