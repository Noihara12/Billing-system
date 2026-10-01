<x-layouts.guest title="Registrasi - PS Rental">
    <h2 class="text-xl font-semibold text-white mb-6">Buat akun baru</h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">Nama Lengkap</label>
            <input
                id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500"
            >
            @error('name')
                <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
            <input
                id="email" type="email" name="email" value="{{ old('email') }}" required
                class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500"
            >
            @error('email')
                <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="whatsapp_number" class="block text-sm font-medium text-slate-300 mb-1.5">Nomor WhatsApp</label>
            <input
                id="whatsapp_number" type="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}" required
                inputmode="numeric" pattern="[0-9]{9,15}" maxlength="15" data-phone
                placeholder="08xxxxxxxxxx"
                class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500"
            >
            @error('whatsapp_number')
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

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">Konfirmasi Password</label>
            <input
                id="password_confirmation" type="password" name="password_confirmation" required
                class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500"
            >
        </div>

        <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 transition">
            Daftar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-400">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-brand-400 hover:text-brand-300 font-medium">Login</a>
    </p>
</x-layouts.guest>
