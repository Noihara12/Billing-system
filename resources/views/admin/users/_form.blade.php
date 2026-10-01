@props(['user', 'roles', 'isEdit' => false])

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">
            Nama Lengkap <span class="text-rose-400">*</span>
        </label>
        <input
            id="name" type="text" name="name"
            value="{{ old('name', $user->name) }}"
            required
            placeholder="Contoh: Budi Santoso"
            class="w-full rounded-lg bg-slate-800 border @error('name') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500"
        >
        @error('name')
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
            Alamat Email <span class="text-rose-400">*</span>
        </label>
        <input
            id="email" type="email" name="email"
            value="{{ old('email', $user->email) }}"
            required
            placeholder="budi@example.com"
            class="w-full rounded-lg bg-slate-800 border @error('email') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500"
        >
        @error('email')
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="whatsapp_number" class="block text-sm font-medium text-slate-300 mb-1.5">
            Nomor WhatsApp <span class="text-rose-400">*</span>
        </label>
        <input
            id="whatsapp_number" type="tel" name="whatsapp_number"
            value="{{ old('whatsapp_number', $user->whatsapp_number) }}"
            required
            inputmode="numeric" pattern="[0-9]{9,15}" maxlength="15" data-phone
            placeholder="081234567890"
            class="w-full rounded-lg bg-slate-800 border @error('whatsapp_number') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono placeholder-slate-500"
        >
        @error('whatsapp_number')
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="role_id" class="block text-sm font-medium text-slate-300 mb-1.5">
            Role Pengguna <span class="text-rose-400">*</span>
        </label>
        <select
            id="role_id" name="role_id" required
            class="w-full rounded-lg bg-slate-800 border @error('role_id') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
        >
            <option value="">-- Pilih Role --</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                    {{ ucfirst($role->name) }}
                </option>
            @endforeach
        </select>
        @error('role_id')
            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 pt-6 border-t border-slate-800">
    <div class="mb-4">
        <h3 class="text-sm font-medium text-white">Keamanan Akun</h3>
        <p class="text-xs text-slate-400 mt-0.5">
            @if ($isEdit)
                Kosongkan kata sandi jika tidak ingin mengubah kata sandi akun pengguna ini.
            @else
                Tentukan kata sandi awal untuk pengguna ini.
            @endif
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                Kata Sandi @if (!$isEdit)<span class="text-rose-400">*</span>@endif
            </label>
            <input
                id="password" type="password" name="password"
                @if (!$isEdit) required @endif
                autocomplete="new-password"
                placeholder="{{ $isEdit ? 'Biarkan kosong untuk mempertahankan' : 'Minimal 8 karakter' }}"
                class="w-full rounded-lg bg-slate-800 border @error('password') border-rose-500 @else border-slate-700 @enderror text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500"
            >
            @error('password')
                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">
                Konfirmasi Kata Sandi @if (!$isEdit)<span class="text-rose-400">*</span>@endif
            </label>
            <input
                id="password_confirmation" type="password" name="password_confirmation"
                @if (!$isEdit) required @endif
                autocomplete="new-password"
                placeholder="Ketik ulang kata sandi"
                class="w-full rounded-lg bg-slate-800 border border-slate-700 text-slate-100 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-500"
            >
        </div>
    </div>
</div>

<div class="mt-8 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-medium px-5 py-2.5 text-sm transition shadow-lg shadow-brand-600/20">
        {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
    </button>
    <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium px-5 py-2.5 text-sm transition">
        Batal
    </a>
</div>

