<x-layouts.admin title="Tambah Pengguna">
    <div class="max-w-3xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-white transition text-sm">
                ← Kembali
            </a>
            <h2 class="text-lg font-bold text-white">Tambah Pengguna Baru</h2>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                @include('admin.users._form', ['user' => $user, 'roles' => $roles, 'isEdit' => false])
            </form>
        </div>
    </div>
</x-layouts.admin>

