<x-layouts.admin title="Edit Game">
    <div class="max-w-3xl">
        <h2 class="text-lg font-semibold text-white mb-5">Edit Game — {{ $game->name }}</h2>

        <form method="POST" action="{{ route('admin.games.update', $game) }}" class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @method('PUT')
            @include('admin.games._form')
        </form>
    </div>
</x-layouts.admin>
