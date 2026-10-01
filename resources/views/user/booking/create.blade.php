<x-layouts.app title="Booking - PS Rental">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-lg font-semibold text-white mb-1">Buat Booking</h2>
        @guest
            <p class="text-sm text-slate-400 mb-5">
                Tidak perlu punya akun. Setelah booking dibuat, Anda mendapat kode booking untuk mengecek atau membatalkannya di halaman
                <a href="{{ route('booking.lookup') }}" class="text-brand-400 hover:text-brand-300">Cek Booking</a>.
            </p>
        @else
            <div class="mb-5"></div>
        @endguest

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('booking.store') }}" class="rounded-xl border border-slate-800 bg-slate-900 p-6 space-y-5">
            @csrf

            <div>
                <label for="unit_id" class="block text-sm font-medium text-slate-300 mb-1.5">Unit PlayStation <span class="text-rose-400">*</span></label>
                <select id="unit_id" name="unit_id" required class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Pilih unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" data-category="{{ $unit->category_id }}" @selected((int) old('unit_id', $selectedUnitId) === $unit->id)>
                            {{ $unit->name }} ({{ $unit->unit_code }}) — {{ $unit->category?->name ?? 'Tanpa kategori' }}
                        </option>
                    @endforeach
                </select>
                @error('unit_id')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="price_list_id" class="block text-sm font-medium text-slate-300 mb-1.5">Durasi &amp; Harga <span class="text-rose-400">*</span></label>
                <select id="price_list_id" name="price_list_id" required class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Pilih unit terlebih dahulu</option>
                    @foreach ($priceLists as $priceList)
                        <option value="{{ $priceList->id }}" data-price="{{ $priceList->price }}" data-category="{{ $priceList->category_id }}" @selected((int) old('price_list_id') === $priceList->id)>
                            {{ $priceList->label }} — Rp {{ number_format($priceList->price, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                @error('price_list_id')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
                <p class="mt-2 text-sm text-slate-400">Harga: <span id="booking-price-preview" class="text-slate-200 font-semibold">Rp 0</span></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-form.input name="booking_date" label="Tanggal Booking" type="date" :value="old('booking_date', now()->toDateString())" min="{{ now()->toDateString() }}" required />
                <x-form.input name="start_time" label="Jam Mulai" type="time" step="1800" :value="old('start_time')" required />
            </div>

            <div id="availability" data-endpoint="{{ route('booking.availability') }}" class="rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <p class="text-sm font-medium text-slate-300">
                        Jadwal Tersedia <span data-availability-hours class="text-xs font-normal text-slate-500"></span>
                    </p>
                    <div class="flex flex-wrap gap-3 text-xs text-slate-400">
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded border border-emerald-500/40 bg-emerald-500/10"></span>Tersedia</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded border border-rose-500/30 bg-rose-500/10"></span>Terisi</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded border border-amber-500/30 bg-amber-500/10"></span>Durasi tidak cukup</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded border border-brand-500 bg-brand-600"></span>Pilihan Anda</span>
                    </div>
                </div>
                <p data-availability-message class="text-sm text-slate-500">Pilih unit dan tanggal untuk melihat jam yang tersedia.</p>
                <div data-availability-slots class="grid grid-cols-4 sm:grid-cols-6 gap-2"></div>
                <p data-availability-busy class="mt-3 text-xs text-slate-500"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-form.input name="customer_name" label="Nama Lengkap" :value="old('customer_name', auth()->user()?->name)" required />
                <x-form.input
                    name="whatsapp_number" label="Nomor WhatsApp" type="tel"
                    :value="old('whatsapp_number', auth()->user()?->whatsapp_number)"
                    inputmode="numeric" pattern="[0-9]{9,15}" maxlength="15" data-phone
                    placeholder="08xxxxxxxxxx" required
                />
            </div>

            <x-form.textarea name="notes" label="Catatan" :value="old('notes')" rows="3" />

            <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 transition">
                Buat Booking
            </button>
        </form>
    </div>

    <script>
        function updateBookingPricePreview(select) {
            const option = select.options[select.selectedIndex];
            const price = option ? Number(option.dataset.price || 0) : 0;
            document.getElementById('booking-price-preview').textContent = 'Rp ' + Math.round(price).toLocaleString('id-ID');
        }
        function filterPriceListsByUnit(unitSelect, priceSelect) {
            const unitOption = unitSelect.options[unitSelect.selectedIndex];
            const categoryId = unitOption ? unitOption.dataset.category || '' : '';
            let visibleCount = 0;

            Array.from(priceSelect.options).forEach((option) => {
                if (!option.value) return;
                const matches = categoryId !== '' && option.dataset.category === categoryId;
                option.hidden = !matches;
                option.disabled = !matches;
                if (matches) visibleCount++;
            });

            if (priceSelect.selectedOptions[0]?.disabled) {
                priceSelect.value = '';
            }

            priceSelect.options[0].textContent = !unitSelect.value
                ? 'Pilih unit terlebih dahulu'
                : (visibleCount ? 'Pilih durasi' : 'Belum ada harga untuk kategori unit ini');

            updateBookingPricePreview(priceSelect);
        }
        const slotStyles = {
            available: 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20 cursor-pointer',
            booked: 'border-rose-500/30 bg-rose-500/10 text-rose-300/70 line-through cursor-not-allowed',
            insufficient: 'border-amber-500/30 bg-amber-500/10 text-amber-300/80 cursor-not-allowed',
            past: 'border-slate-800 bg-slate-900 text-slate-600 cursor-not-allowed',
            selected: 'border-brand-500 bg-brand-600 text-white',
            covered: 'border-brand-500/40 bg-brand-500/20 text-brand-200',
        };
        const slotTitles = {
            available: 'Tersedia',
            booked: 'Sudah terisi',
            insufficient: 'Durasi yang dipilih bentrok dengan jadwal berikutnya atau melewati jam tutup',
            past: 'Jam sudah lewat',
        };
        let availabilityData = null;
        let availabilityController = null;

        // Menit sejak jam buka; jam setelah tengah malam (jam operasional lewat hari) tetap berurutan.
        function toMinutes(time) {
            const [h, m] = time.split(':').map(Number);
            const [openH, openM] = (availabilityData?.open || '00:00').split(':').map(Number);
            const minutes = h * 60 + m;
            return minutes < openH * 60 + openM ? minutes + 1440 : minutes;
        }

        function renderSlots() {
            const root = document.getElementById('availability');
            const grid = root.querySelector('[data-availability-slots]');
            grid.replaceChildren();
            if (!availabilityData) return;

            const startTime = document.getElementById('start_time').value;
            const start = startTime ? toMinutes(startTime) : null;
            const end = start !== null ? start + availabilityData.duration_minutes : null;

            availabilityData.slots.forEach((slot) => {
                const slotMinutes = toMinutes(slot.time);
                let style = slotStyles[slot.status];
                if (slot.time === startTime && slot.status === 'available') {
                    style = slotStyles.selected;
                } else if (start !== null && slotMinutes > start && slotMinutes < end && slot.status !== 'booked') {
                    style = slotStyles.covered;
                }

                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = slot.time;
                button.title = slotTitles[slot.status];
                button.disabled = slot.status !== 'available';
                button.className = `rounded-md border px-2 py-1.5 text-sm font-medium transition ${style}`;
                button.addEventListener('click', () => {
                    document.getElementById('start_time').value = slot.time;
                    renderSlots();
                });
                grid.append(button);
            });
        }

        async function loadAvailability() {
            const root = document.getElementById('availability');
            const message = root.querySelector('[data-availability-message]');
            const busy = root.querySelector('[data-availability-busy]');
            const hours = root.querySelector('[data-availability-hours]');
            const unitId = document.getElementById('unit_id').value;
            const date = document.getElementById('booking_date').value;
            const priceListId = document.getElementById('price_list_id').value;

            availabilityData = null;
            busy.textContent = '';
            hours.textContent = '';
            renderSlots();

            if (!unitId || !date) {
                message.textContent = 'Pilih unit dan tanggal untuk melihat jam yang tersedia.';
                message.hidden = false;
                return;
            }

            availabilityController?.abort();
            availabilityController = new AbortController();
            message.textContent = 'Memuat jadwal...';
            message.hidden = false;

            try {
                const params = new URLSearchParams({ unit_id: unitId, date });
                if (priceListId) params.set('price_list_id', priceListId);
                const res = await fetch(`${root.dataset.endpoint}?${params}`, {
                    headers: { Accept: 'application/json' },
                    signal: availabilityController.signal,
                });
                if (!res.ok) throw new Error('Request failed');

                availabilityData = await res.json();
                hours.textContent = `(jam operasional ${availabilityData.open}–${availabilityData.close})`;
                const hasAvailable = availabilityData.slots.some((slot) => slot.status === 'available');
                message.textContent = hasAvailable
                    ? (priceListId ? '' : 'Pilih durasi agar slot yang bentrok dengan durasi ikut ditandai.')
                    : 'Tidak ada jam tersedia pada tanggal ini. Silakan pilih tanggal lain.';
                message.hidden = message.textContent === '';
                busy.textContent = availabilityData.busy.length
                    ? 'Sudah terisi: ' + availabilityData.busy.map((range) => `${range.start}–${range.end}`).join(', ')
                    : '';

                const selected = document.getElementById('start_time').value;
                const selectedSlot = availabilityData.slots.find((slot) => slot.time === selected);
                if (selected && selectedSlot && selectedSlot.status !== 'available') {
                    document.getElementById('start_time').value = '';
                }
                renderSlots();
            } catch (error) {
                if (error.name === 'AbortError') return;
                message.textContent = 'Gagal memuat jadwal. Silakan coba lagi.';
                message.hidden = false;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const unitSelect = document.getElementById('unit_id');
            const priceSelect = document.getElementById('price_list_id');

            unitSelect.addEventListener('change', () => {
                filterPriceListsByUnit(unitSelect, priceSelect);
                loadAvailability();
            });
            priceSelect.addEventListener('change', () => {
                updateBookingPricePreview(priceSelect);
                loadAvailability();
            });
            document.getElementById('booking_date').addEventListener('change', loadAvailability);
            document.getElementById('start_time').addEventListener('input', renderSlots);

            filterPriceListsByUnit(unitSelect, priceSelect);
            loadAvailability();
        });
    </script>
</x-layouts.app>
