<x-layouts.admin title="Tambah Kategori">
    <div class="max-w-2xl">
        <h2 class="text-lg font-semibold text-white mb-5">Tambah Kategori</h2>

        <form method="POST" action="{{ route('admin.categories.store') }}" class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @include('admin.categories._form')
        </form>
    </div>
</x-layouts.admin>
