<x-layouts.admin title="Active Rental">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-white">Active Rental</h2>
        <a href="{{ route('admin.billing.create') }}" class="rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-medium px-4 py-2 transition">
            + Start Rental
        </a>
    </div>

    <div data-rentals-fragment data-fragment-endpoint="{{ route('admin.rentals.data') }}">
        @include('admin.rentals._cards')
    </div>
</x-layouts.admin>
