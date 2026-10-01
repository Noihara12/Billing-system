// Countdown & dashboard stats are driven by server time, never a lone client timer.
function formatRupiah(value) {
    const rounded = Math.round(Number(value) || 0);
    return 'Rp ' + rounded.toLocaleString('id-ID');
}

function formatDuration(totalSeconds) {
    totalSeconds = Math.max(0, Math.floor(totalSeconds));
    const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
    const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
    const s = String(totalSeconds % 60).padStart(2, '0');
    return `${h}:${m}:${s}`;
}

function startCountdown(el) {
    const endIso = el.dataset.remainingEnd;
    if (!endIso) return;

    if (el._countdownTimer) {
        clearInterval(el._countdownTimer);
    }

    const endMs = new Date(endIso).getTime();

    const update = () => {
        const remainingSeconds = (endMs - Date.now()) / 1000;
        el.textContent = formatDuration(remainingSeconds);
        el.classList.toggle('text-rose-400', remainingSeconds <= 0);
    };

    update();
    el._countdownTimer = setInterval(update, 1000);
}

function initCountdowns(root = document) {
    root.querySelectorAll('[data-remaining-end]').forEach(startCountdown);
}

function statusBadgeHtml(status) {
    const styles = {
        AVAILABLE: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
        BOOKED: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
        PLAYING: 'bg-brand-500/15 text-brand-400 border-brand-500/30',
        MAINTENANCE: 'bg-orange-500/15 text-orange-400 border-orange-500/30',
        OFFLINE: 'bg-slate-500/15 text-slate-400 border-slate-500/30',
        PENDING: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
        CONFIRMED: 'bg-brand-500/15 text-brand-400 border-brand-500/30',
        CANCELLED: 'bg-rose-500/15 text-rose-400 border-rose-500/30',
        COMPLETED: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
        ACTIVE: 'bg-brand-500/15 text-brand-400 border-brand-500/30',
    };
    const cls = styles[status] || styles.OFFLINE;
    return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border ${cls}">${status}</span>`;
}

function updateStatCards(root, stats) {
    Object.entries(stats || {}).forEach(([key, value]) => {
        const cardRoot = root.querySelector(`[data-stat="${key}"]`);
        const valueEl = cardRoot?.querySelector('[data-stat-value]');
        if (!valueEl) return;
        valueEl.textContent = cardRoot.dataset.format === 'currency' ? formatRupiah(value) : value;
    });
}

function renderActiveRentals(tbody, rentals) {
    tbody.querySelectorAll('[data-remaining-end]').forEach((el) => {
        if (el._countdownTimer) clearInterval(el._countdownTimer);
    });

    if (!rentals.length) {
        tbody.innerHTML = '<tr data-empty-row><td colspan="6" class="px-5 py-6 text-center text-slate-500">Belum ada rental aktif.</td></tr>';
        return;
    }

    tbody.innerHTML = rentals.map((rental) => `
        <tr>
            <td class="px-5 py-3 text-slate-200">${rental.unit_name}</td>
            <td class="px-5 py-3 text-slate-200">${rental.customer_name}</td>
            <td class="px-5 py-3 text-slate-400">${rental.start_time}</td>
            <td class="px-5 py-3 text-slate-400">${rental.end_time}</td>
            <td class="px-5 py-3 font-mono text-slate-200" data-remaining-end="${rental.end_time_iso}">-</td>
            <td class="px-5 py-3">${statusBadgeHtml(rental.status)}</td>
        </tr>
    `).join('');

    initCountdowns(tbody);
}

function renderUnitCard(unit) {
    let timeBlock = '<p class="text-sm text-slate-400 mb-1">Sisa waktu: <span class="text-slate-200 font-mono">-</span></p>';

    if (unit.status === 'PLAYING' && unit.remaining_end_iso) {
        timeBlock = `<p class="text-sm text-slate-400 mb-1">Sisa waktu: <span class="text-slate-200 font-mono" data-remaining-end="${unit.remaining_end_iso}">-</span></p>`;
    } else if (unit.status === 'BOOKED' && unit.booking_start) {
        const hours = Math.round((unit.booking_duration_minutes / 60) * 10) / 10;
        timeBlock = `
            <p class="text-sm text-slate-400">Mulai: <span class="text-slate-200">${unit.booking_start}</span></p>
            <p class="text-sm text-slate-400">Durasi: <span class="text-slate-200">${hours} Jam</span></p>
        `;
    }

    const gamesBlock = unit.games && unit.games.length
        ? `<p class="text-xs text-slate-500 mt-3">Game: ${unit.games.join(', ')}</p>`
        : '';

    // Unit yang sedang dipakai tetap bisa dibooking untuk jam lain; hanya MAINTENANCE/OFFLINE yang ditutup.
    const bookingButton = ['MAINTENANCE', 'OFFLINE'].includes(unit.status)
        ? '<span class="mt-4 block w-full text-center rounded-lg bg-slate-800 text-slate-500 text-sm font-medium py-2 cursor-not-allowed">Sedang Tidak Tersedia</span>'
        : `<a href="/booking?unit_id=${unit.id}" class="mt-4 inline-block w-full text-center rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium py-2 transition">${unit.status === 'AVAILABLE' ? 'Booking' : 'Booking untuk Jam Lain'}</a>`;

    return `
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-5" data-unit-id="${unit.id}">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-white">${unit.name}</h3>
                    <p class="text-xs text-slate-500">${unit.unit_code}</p>
                </div>
                ${statusBadgeHtml(unit.status)}
            </div>
            ${timeBlock}
            ${gamesBlock}
            ${bookingButton}
        </div>
    `;
}

function renderUnits(grid, units) {
    grid.querySelectorAll('[data-remaining-end]').forEach((el) => {
        if (el._countdownTimer) clearInterval(el._countdownTimer);
    });

    if (!units.length) {
        grid.innerHTML = '<p class="text-slate-500 text-sm col-span-full" data-empty-row>Belum ada unit yang tersedia.</p>';
        return;
    }

    grid.innerHTML = units.map(renderUnitCard).join('');
    initCountdowns(grid);
}

function initCarousels(root = document) {
    root.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
        if (slides.length <= 1 || carousel._carouselInitialized) return;
        carousel._carouselInitialized = true;

        const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
        let index = 0;
        let timer;

        const show = (i) => {
            index = (i + slides.length) % slides.length;
            slides.forEach((slide, si) => slide.classList.toggle('hidden', si !== index));
            dots.forEach((dot, di) => {
                dot.classList.toggle('bg-white', di === index);
                dot.classList.toggle('bg-white/40', di !== index);
            });
        };

        const next = () => show(index + 1);
        const prev = () => show(index - 1);
        const restart = () => {
            clearInterval(timer);
            timer = setInterval(next, 5000);
        };

        carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => { next(); restart(); });
        carousel.querySelector('[data-carousel-prev]')?.addEventListener('click', () => { prev(); restart(); });
        dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); restart(); }));

        timer = setInterval(next, 5000);
    });
}

async function refreshDashboard(root) {
    const endpoint = root.dataset.endpoint;
    if (!endpoint) return;

    try {
        const res = await fetch(endpoint, { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const payload = await res.json();

        if (payload.stats) {
            updateStatCards(root, payload.stats);
        }

        const tbody = root.querySelector('[data-active-rentals-body]');
        if (tbody && payload.active_rentals) {
            renderActiveRentals(tbody, payload.active_rentals);
        }

        const unitsGrid = root.querySelector('[data-units-grid]');
        if (unitsGrid && payload.units) {
            renderUnits(unitsGrid, payload.units);
        }
    } catch (error) {
        // Network hiccup: keep last known state and let the next poll retry.
    }
}

async function refreshRentalsFragment(container) {
    const endpoint = container.dataset.fragmentEndpoint;
    if (!endpoint) return;

    try {
        const res = await fetch(endpoint);
        if (!res.ok) return;
        const html = await res.text();

        container.querySelectorAll('[data-remaining-end]').forEach((el) => {
            if (el._countdownTimer) clearInterval(el._countdownTimer);
        });

        container.innerHTML = html;
        initCountdowns(container);
    } catch (error) {
        // Network hiccup: keep last known state and let the next poll retry.
    }
}

// Dropdown multi-pilih yang membungkus <select multiple data-multiselect>; select asli tetap menjadi sumber data form.
function initMultiSelects(root = document) {
    root.querySelectorAll('select[multiple][data-multiselect]').forEach((select) => {
        if (select._multiselectInitialized) return;
        select._multiselectInitialized = true;

        const options = Array.from(select.options);
        const make = (tag, className, text) => {
            const el = document.createElement(tag);
            if (className) el.className = className;
            if (text !== undefined) el.textContent = text;
            return el;
        };

        const wrapper = make('div', 'relative');
        const control = make('div', 'w-full min-h-[42px] flex items-center gap-2 rounded-lg bg-slate-800 border border-slate-700 pl-2 pr-3 py-1.5 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500');
        control.tabIndex = 0;
        control.setAttribute('role', 'combobox');
        control.setAttribute('aria-haspopup', 'listbox');
        control.setAttribute('aria-expanded', 'false');

        const tags = make('div', 'flex-1 flex flex-wrap gap-1.5');
        const chevron = make('span', 'text-slate-400 text-xs transition-transform', '▼');
        control.append(tags, chevron);

        // Panel ikut alur dokumen (bukan absolute) agar card form ikut memanjang dan panel tidak keluar dari form.
        const panel = make('div', 'mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 [color-scheme:dark] hidden');
        const search = make('input', 'w-full bg-transparent border-b border-slate-700 text-slate-100 text-sm px-3 py-2 focus:outline-none');
        search.type = 'text';
        search.placeholder = select.dataset.searchPlaceholder || 'Cari...';

        const list = make('ul', 'max-h-60 overflow-y-auto py-1');
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-multiselectable', 'true');

        const footer = make('div', 'flex items-center justify-between border-t border-slate-700 px-3 py-2 text-xs');
        const counter = make('span', 'text-slate-500');
        const clearButton = make('button', 'text-rose-400 hover:text-rose-300', 'Hapus semua');
        clearButton.type = 'button';
        footer.append(counter, clearButton);

        panel.append(search, list, footer);
        wrapper.append(control, panel);
        select.classList.add('hidden');
        select.after(wrapper);

        const isOpen = () => !panel.classList.contains('hidden');
        const toggleOption = (option, selected = !option.selected) => {
            option.selected = selected;
            render();
            select.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const renderTags = () => {
            const selected = options.filter((option) => option.selected);
            tags.replaceChildren();

            if (!selected.length) {
                tags.append(make('span', 'px-1 py-0.5 text-slate-500', select.dataset.placeholder || 'Pilih...'));
            }

            selected.forEach((option) => {
                const name = option.textContent.trim();
                const tag = make('span', 'inline-flex items-center gap-1 rounded-full bg-brand-500/20 border border-brand-500/40 text-brand-200 text-xs font-medium pl-2.5 pr-1 py-0.5', name);
                const remove = make('button', 'rounded-full w-4 h-4 leading-none text-brand-300 hover:bg-brand-500/40 hover:text-white', '×');
                remove.type = 'button';
                remove.setAttribute('aria-label', `Hapus ${name}`);
                remove.addEventListener('click', (event) => {
                    event.stopPropagation();
                    toggleOption(option, false);
                });
                tag.append(remove);
                tags.append(tag);
            });

            counter.textContent = `${selected.length} dipilih`;
            clearButton.classList.toggle('invisible', !selected.length);
        };

        const matchingOptions = () => {
            const term = search.value.trim().toLowerCase();
            return options.filter((option) => option.textContent.toLowerCase().includes(term));
        };

        const renderList = () => {
            const matches = matchingOptions();
            list.replaceChildren();

            if (!matches.length) {
                list.append(make('li', 'px-3 py-2 text-sm text-slate-500', 'Tidak ditemukan'));
                return;
            }

            matches.forEach((option) => {
                const item = make('li', `flex items-center gap-2 px-3 py-2 text-sm cursor-pointer hover:bg-slate-800 ${option.selected ? 'text-brand-200' : 'text-slate-300'}`);
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', String(option.selected));
                const box = make('span', `flex items-center justify-center w-4 h-4 rounded border text-[10px] ${option.selected ? 'bg-brand-500 border-brand-500 text-white' : 'border-slate-600'}`, option.selected ? '✓' : '');
                item.append(box, make('span', '', option.textContent.trim()));
                item.addEventListener('mousedown', (event) => event.preventDefault());
                item.addEventListener('click', (event) => {
                    event.stopPropagation();
                    toggleOption(option);
                });
                list.append(item);
            });
        };

        const render = () => {
            renderTags();
            renderList();
        };

        const open = () => {
            panel.classList.remove('hidden');
            control.setAttribute('aria-expanded', 'true');
            chevron.classList.add('rotate-180');
            search.focus();
        };

        const close = () => {
            panel.classList.add('hidden');
            control.setAttribute('aria-expanded', 'false');
            chevron.classList.remove('rotate-180');
            search.value = '';
            renderList();
        };

        control.addEventListener('click', () => (isOpen() ? close() : open()));
        control.addEventListener('keydown', (event) => {
            if (['Enter', ' ', 'ArrowDown'].includes(event.key)) {
                event.preventDefault();
                open();
            }
        });
        search.addEventListener('input', renderList);
        search.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
                control.focus();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                const [first] = matchingOptions();
                if (first) toggleOption(first);
            }
        });
        clearButton.addEventListener('click', (event) => {
            event.stopPropagation();
            options.forEach((option) => { option.selected = false; });
            render();
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        document.addEventListener('click', (event) => {
            if (isOpen() && !wrapper.contains(event.target)) close();
        });        if (select.id) {
            document.querySelectorAll(`label[for="${select.id}"]`).forEach((label) => {
                label.addEventListener('click', (event) => {
                    event.preventDefault();
                    control.focus();
                });
            });
        }

        render();
    });
}

// Sidebar admin sebagai drawer di layar kecil; di lg ke atas sidebar selalu tampil sehingga tidak perlu toggle.
function initAdminSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    if (!sidebar || !overlay) return;

    const open = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    };

    const close = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
    };

    document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', open);
    document.querySelector('[data-sidebar-close]')?.addEventListener('click', close);
    overlay.addEventListener('click', close);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
}

// Nomor WhatsApp hanya angka; karakter lain (spasi, tanda hubung, +) dibuang saat mengetik atau menempel.
function initPhoneInputs(root = document) {
    root.querySelectorAll('input[data-phone]').forEach((input) => {
        input.addEventListener('input', () => {
            const digitsOnly = input.value.replace(/\D/g, '');
            if (digitsOnly !== input.value) {
                const cursor = input.selectionStart - (input.value.length - digitsOnly.length);
                input.value = digitsOnly;
                input.setSelectionRange(cursor, cursor);
            }
        });
    });
}

// Menu customer di layar kecil: ikon di navbar membuka/menutup daftar halaman.
function initNavbarMenu() {
    const toggle = document.querySelector('[data-navbar-toggle]');
    const panel = document.querySelector('[data-navbar-panel]');
    if (!toggle || !panel) return;

    const setOpen = (open) => {
        panel.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', () => setOpen(panel.classList.contains('hidden')));
    document.addEventListener('click', (event) => {
        if (!panel.contains(event.target) && !toggle.contains(event.target)) setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initNavbarMenu();
    initPhoneInputs();
    initCountdowns();
    initCarousels();
    initMultiSelects();
    initAdminSidebar();

    const dashboardRoot = document.querySelector('[data-dashboard-root]');
    if (dashboardRoot) {
        setInterval(() => refreshDashboard(dashboardRoot), 10000);
    }

    const rentalsFragment = document.querySelector('[data-rentals-fragment]');
    if (rentalsFragment) {
        setInterval(() => refreshRentalsFragment(rentalsFragment), 10000);
    }
});
