<x-layouts.admin title="Edit Unit">
    <div class="max-w-3xl">
        <h2 class="text-lg font-semibold text-white mb-5">Edit Unit — {{ $unit->name }}</h2>

        <form method="POST" action="{{ route('admin.units.update', $unit) }}" class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @method('PUT')
            @include('admin.units._form')
        </form>
    </div>
</x-layouts.admin>
