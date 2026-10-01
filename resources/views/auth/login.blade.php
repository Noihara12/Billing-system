<x-layouts.guest title="Login - PS Rental">
    <h2 class="text-xl font-semibold text-white mb-6">Masuk ke akun Anda</h2>

    @if (session('status'))
        <div class="mb-4 text-sm text-emerald-400">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
            <input
                id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500"
            >
            @error('email')
                <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
            <input
                id="password" type="password" name="password" required
                class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500"
            >
            @error('password')
                <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-800 text-brand-500 focus:ring-brand-500">
            Ingat saya
        </label>

        <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 transition">
            Login
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-400">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300 font-medium">Daftar</a>
    </p>
</x-layouts.guest>
