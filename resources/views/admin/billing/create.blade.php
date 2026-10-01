<x-layouts.admin title="Billing">
    <h2 class="text-lg font-semibold text-white mb-5">Billing — Start Rental</h2>

    <div class="mb-8">
        <h3 class="text-sm font-semibold text-slate-300 mb-3">Booking Menunggu Rental</h3>
        <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-slate-800/50 text-slate-400 text-left">
                    <tr>
                        <th class="px-5 py-3 font-medium">Kode</th>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Unit</th>
                        <th class="px-5 py-3 font-medium">Jadwal</th>
                        <th class="px-5 py-3 font-medium">Durasi</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($pendingBookings as $booking)
                        <tr>
                            <td class="px-5 py-3 font-mono text-xs text-slate-300">{{ $booking->booking_code }}</td>
                            <td class="px-5 py-3 text-slate-200">{{ $booking->customer_name }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $booking->unit->name }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $booking->booking_date->format('d M Y') }} {{ $booking->start_time->format('H:i') }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ round($booking->duration_minutes / 60, 1) }} Jam</td>
                            <td class="px-5 py-3 text-right">
                                <form method="POST" action="{{ route('admin.billing.start-from-booking', $booking) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-medium px-3 py-1.5 transition">
                                        START RENTAL
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-slate-500">Tidak ada booking yang menunggu rental.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </div>

    <div>
        <h3 class="text-sm font-semibold text-slate-300 mb-3">Walk-in Rental (Tanpa Booking)</h3>
        <div class="max-w-2xl rounded-xl border border-slate-800 bg-slate-900 p-6">
            <form method="POST" action="{{ route('admin.billing.start-walkin') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="unit_id" class="block text-sm font-medium text-slate-300 mb-1.5">Unit PlayStation <span class="text-rose-400">*</span></label>
                    <select id="unit_id" name="unit_id" required onchange="filterPriceListsByUnit(this, document.getElementById('price_list_id'))" class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Pilih unit (hanya yang AVAILABLE)</option>
                        @foreach ($availableUnits as $unit)
                            @php $limit = $walkInLimits[$unit->id]; @endphp
                            <option
                                value="{{ $unit->id }}"
                                data-category="{{ $unit->category_id }}"
                                data-max-minutes="{{ $limit['minutes_available'] }}"
                                data-next-booking="{{ $limit['next_booking_start']?->format('d M H:i') }}"
                                @selected((int) old('unit_id', $selectedUnitId) === $unit->id)
                            >
                                {{ $unit->name }} ({{ $unit->unit_code }}) — {{ $unit->category?->name ?? 'Tanpa kategori' }}{{ $limit['next_booking_start'] ? ' · booking '.$limit['next_booking_start']->format('d M H:i') : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
                    <p id="walkin-hint" class="mt-2 text-sm text-amber-400" hidden></p>
                </div>

                <div>
                    <label for="user_id" class="block text-sm font-medium text-slate-300 mb-1.5">Customer Terdaftar (opsional)</label>
                    <select id="user_id" name="user_id" onchange="fillWalkInCustomer(this)" class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Walk-in / tanpa akun</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-whatsapp="{{ $customer->whatsapp_number }}" @selected((int) old('user_id') === $customer->id)>
                                {{ $customer->name }} ({{ $customer->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-form.input name="customer_name" label="Nama Customer" :value="old('customer_name')" required />
                    <x-form.input
                        name="whatsapp_number" label="Nomor WhatsApp (opsional)" type="tel" :value="old('whatsapp_number')"
                        inputmode="numeric" pattern="[0-9]{9,15}" maxlength="15" data-phone placeholder="08xxxxxxxxxx"
                    />
                </div>

                <div>
                    <label for="price_list_id" class="block text-sm font-medium text-slate-300 mb-1.5">Durasi &amp; Harga <span class="text-rose-400">*</span></label>
                    <select id="price_list_id" name="price_list_id" required onchange="updateBillingPricePreview(this)" class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Pilih unit terlebih dahulu</option>
                        @foreach ($priceLists as $priceList)
                            @php $priceLabel = $priceList->label.' — Rp '.number_format($priceList->price, 0, ',', '.'); @endphp
                            <option value="{{ $priceList->id }}" data-price="{{ $priceList->price }}" data-category="{{ $priceList->category_id }}" data-duration="{{ $priceList->duration_minutes }}" data-label="{{ $priceLabel }}" @selected((int) old('price_list_id') === $priceList->id)>
                                {{ $priceLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('price_list_id')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
                    <p class="mt-2 text-sm text-slate-400">Harga: <span id="billing-price-preview" class="text-slate-200 font-semibold">Rp 0</span></p>
                </div>

                <button type="submit" class="w-full rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 transition">
                    START RENTAL
                </button>
            </form>
        </div>
    </div>

    <script>
        function updateBillingPricePreview(select) {
            const option = select.options[select.selectedIndex];
            const price = option ? Number(option.dataset.price || 0) : 0;
            document.getElementById('billing-price-preview').textContent = 'Rp ' + Math.round(price).toLocaleString('id-ID');
        }
        function fillWalkInCustomer(select) {
            const option = select.options[select.selectedIndex];
            if (!option || !option.dataset.name) return;
            document.getElementById('customer_name').value = option.dataset.name;
            document.getElementById('whatsapp_number').value = option.dataset.whatsapp || '';
        }
        function filterPriceListsByUnit(unitSelect, priceSelect) {
            const unitOption = unitSelect.options[unitSelect.selectedIndex];
            const categoryId = unitOption ? unitOption.dataset.category || '' : '';
            const maxMinutes = unitOption && unitOption.dataset.maxMinutes !== '' && unitOption.dataset.maxMinutes !== undefined
                ? Number(unitOption.dataset.maxMinutes)
                : null;
            const hint = document.getElementById('walkin-hint');
            let visibleCount = 0;

            Array.from(priceSelect.options).forEach((option) => {
                if (!option.value) return;
                const matches = categoryId !== '' && option.dataset.category === categoryId;
                const tooLong = maxMinutes !== null && Number(option.dataset.duration) > maxMinutes;
                option.hidden = !matches;
                option.disabled = !matches || tooLong;
                option.textContent = option.dataset.label + (matches && tooLong ? ' (bentrok dengan booking)' : '');
                if (matches && !tooLong) visibleCount++;
            });

            if (maxMinutes !== null && unitSelect.value) {
                hint.textContent = maxMinutes > 0
                    ? `Unit ini ada booking ${unitOption.dataset.nextBooking}. Walk-in maksimal ${maxMinutes} menit (selesai ${@json(\App\Services\BookingService::BOOKED_LEAD_MINUTES)} menit sebelum booking).`
                    : `Unit ini ada booking ${unitOption.dataset.nextBooking}, tidak bisa dipakai walk-in sekarang.`;
                hint.hidden = false;
            } else {
                hint.hidden = true;
            }

            if (priceSelect.selectedOptions[0]?.disabled) {
                priceSelect.value = '';
            }

            priceSelect.options[0].textContent = !unitSelect.value
                ? 'Pilih unit terlebih dahulu'
                : (visibleCount ? 'Pilih durasi' : 'Tidak ada durasi yang bisa dipilih untuk unit ini');

            updateBillingPricePreview(priceSelect);
        }
        document.addEventListener('DOMContentLoaded', () => {
            filterPriceListsByUnit(document.getElementById('unit_id'), document.getElementById('price_list_id'));
        });
    </script>
</x-layouts.admin>
