<x-layouts.app title="Cek Booking - PS Rental">
    <div class="max-w-md mx-auto">
        <h2 class="text-lg font-semibold text-white mb-1">Cek Booking</h2>
        <p class="text-sm text-slate-400 mb-5">Masukkan kode booking dan nomor WhatsApp yang Anda pakai saat booking.</p>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('booking.lookup.store') }}" class="rounded-xl border border-slate-800 bg-slate-900 p-6 space-y-5">
            @csrf

            <x-form.input name="booking_code" label="Kode Booking" :value="old('booking_code')" placeholder="BK2609ABCD" required />
            <x-form.input
                name="whatsapp_number" label="Nomor WhatsApp" type="tel" :value="old('whatsapp_number')"
                inputmode="numeric" pattern="[0-9]{9,15}" maxlength="15" data-phone
                placeholder="08xxxxxxxxxx" required
            />

            <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 transition">
                Cari Booking
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-500">
            Belum punya booking? <a href="{{ route('booking.create') }}" class="text-brand-400 hover:text-brand-300">Buat booking</a>
        </p>
    </div>
</x-layouts.app>
