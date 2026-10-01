<x-layouts.admin title="Tambah Game">
    <div class="max-w-3xl">
        <h2 class="text-lg font-semibold text-white mb-5">Tambah Game</h2>

        <form method="POST" action="{{ route('admin.games.store') }}" class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @include('admin.games._form')
        </form>
    </div>
</x-layouts.admin>
