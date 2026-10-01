<x-layouts.admin title="Edit Kategori">
    <div class="max-w-2xl">
        <h2 class="text-lg font-semibold text-white mb-5">Edit Kategori — {{ $category->name }}</h2>

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @method('PUT')
            @include('admin.categories._form')
        </form>
    </div>
</x-layouts.admin>
