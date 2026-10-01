<x-layouts.admin title="Settings">
    <div class="max-w-2xl">
        <h2 class="text-lg font-semibold text-white mb-5">Settings</h2>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @method('PUT')

            <h3 class="text-base font-semibold text-white">Jam Operasional Booking</h3>
            <p class="mt-1 text-sm text-slate-400">Berlaku untuk semua hari. Customer hanya bisa booking di dalam rentang jam ini (slot per 30 menit).</p>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-form.input name="booking_open_time" label="Jam Buka" type="time" step="1800" :value="$openTime" required />
                <x-form.input name="booking_close_time" label="Jam Tutup" type="time" step="1800" :value="$closeTime" required />
            </div>

            <div class="mt-4 rounded-lg border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm">
                <p class="text-slate-400">
                    Saat ini:
                    <span class="font-semibold text-slate-100">{{ $operatingHours['open'] }} – {{ $operatingHours['close'] }}</span>
                    <span class="text-slate-500">({{ $operatingHours['minutes'] % 60 ? number_format($operatingHours['minutes'] / 60, 1, ',', '') : intdiv($operatingHours['minutes'], 60) }} jam{{ $operatingHours['overnight'] ? ', sampai dini hari berikutnya' : '' }})</span>
                </p>
                <ul class="mt-2 list-disc pl-5 text-xs text-slate-500 space-y-1">
                    <li>Isi jam tutup <span class="text-slate-300">00:00</span> untuk buka sampai tengah malam (24:00).</li>
                    <li>Jam tutup yang lebih awal dari jam buka dianggap hari berikutnya, mis. buka 10:00 tutup 02:00.</li>
                    <li>Jam buka sama dengan jam tutup berarti buka 24 jam.</li>
                    <li>Perubahan tidak membatalkan booking yang sudah ada.</li>
                </ul>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium px-5 py-2.5 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
