<x-layouts.admin title="Edit Price List">
    <div class="max-w-2xl">
        <h2 class="text-lg font-semibold text-white mb-5">Edit Price List — {{ $priceList->label }}</h2>

        <form method="POST" action="{{ route('admin.price-lists.update', $priceList) }}" class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            @csrf
            @method('PUT')
            @include('admin.price-lists._form')
        </form>
    </div>
</x-layouts.admin>
