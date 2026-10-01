<x-layouts.app title="Tentang Kami - Bagoes Cafe">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <img src="{{ asset('images/logo-bagoes.jpg') }}" alt="{{ config('contact.name') }}" class="w-16 h-16 rounded-xl object-cover">
            <div>
                <h2 class="text-xl font-semibold text-white">{{ config('contact.name') }}</h2>
                <p class="text-sm text-slate-400">Rental PlayStation 4 &amp; 5 — main di tempat, bisa booking jam sesuka Anda.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                <h3 class="font-semibold text-white">Lokasi</h3>
                <p class="mt-1 text-sm text-slate-400">{{ config('contact.place_name') }}</p>

                <div class="mt-4 rounded-lg overflow-hidden border border-slate-800">
                    <iframe
                        src="{{ config('contact.maps_embed') }}"
                        title="Peta lokasi {{ config('contact.name') }}"
                        class="w-full h-64" style="border:0;"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen
                    ></iframe>
                </div>

                <a href="{{ config('contact.maps_url') }}" target="_blank" rel="noopener"
                   class="mt-4 block w-full text-center rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium py-2.5 transition">
                    Buka di Google Maps
                </a>
            </div>

            <div class="space-y-5">
                <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                    <h3 class="font-semibold text-white">Hubungi Kami</h3>
                    <p class="mt-1 text-sm text-slate-400">Tanya ketersediaan unit, harga, atau konfirmasi booking lewat WhatsApp.</p>

                    <p class="mt-4 text-lg font-semibold text-slate-100">{{ config('contact.whatsapp_display') }}</p>

                    <a href="https://wa.me/{{ config('contact.whatsapp') }}" target="_blank" rel="noopener"
                       class="mt-4 block w-full text-center rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium py-2.5 transition">
                        Chat via WhatsApp
                    </a>
                </div>

                <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                    <h3 class="font-semibold text-white">Jam Operasional</h3>
                    <p class="mt-2 text-slate-200">
                        Setiap hari
                        <span class="font-semibold">{{ $operatingHours['open'] }} – {{ $operatingHours['close'] }}</span>
                    </p>
                    <p class="mt-1 text-sm text-slate-500">Booking online mengikuti jam ini.</p>

                    <a href="{{ route('booking.create') }}"
                       class="mt-4 block w-full text-center rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium py-2.5 transition">
                        Booking Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
