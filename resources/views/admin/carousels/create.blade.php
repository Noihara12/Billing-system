<x-layouts.admin title="Tambah Carousel">
    <div class="max-w-2xl">
        <h2 class="text-lg font-semibold text-white mb-5">Tambah Carousel</h2>

        <form method="POST" action="{{ route('admin.carousels.store') }}" enctype="multipart/form-data" class="rounded-xl border border-slate-800 bg-slate-900 p-6 space-y-5">
            @csrf
            @include('admin.carousels._form')
        </form>
    </div>
</x-layouts.admin>
