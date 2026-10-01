<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePriceListRequest;
use App\Http\Requests\Admin\UpdatePriceListRequest;
use App\Models\Category;
use App\Models\PriceList;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriceListController extends Controller
{
    public function index(): View
    {
        $orderPriceLists = fn ($q) => $q->orderBy('sort_order')->orderBy('duration_minutes');

        return view('admin.price-lists.index', [
            'categories' => Category::query()
                ->with(['priceLists' => $orderPriceLists])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'uncategorizedPriceLists' => $orderPriceLists(PriceList::query()->whereNull('category_id'))->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.price-lists.create', [
            'priceList' => new PriceList([
                'category_id' => $request->integer('category_id') ?: null,
                'is_active' => true,
                'sort_order' => 0,
            ]),
            'categories' => $this->categories(),
        ]);
    }

    public function store(StorePriceListRequest $request): RedirectResponse
    {
        $priceList = PriceList::create($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.price-lists.index')->with('status', "Price list \"{$priceList->label}\" berhasil ditambahkan.");
    }

    public function edit(PriceList $priceList): View
    {
        return view('admin.price-lists.edit', [
            'priceList' => $priceList,
            'categories' => $this->categories(),
        ]);
    }

    public function update(UpdatePriceListRequest $request, PriceList $priceList): RedirectResponse
    {
        $priceList->update($request->validated() + [
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.price-lists.index')->with('status', "Price list \"{$priceList->label}\" berhasil diperbarui.");
    }

    public function destroy(PriceList $priceList): RedirectResponse
    {
        $priceList->delete();

        return redirect()->route('admin.price-lists.index')->with('status', "Price list \"{$priceList->label}\" berhasil dihapus.");
    }

    public function toggle(PriceList $priceList): RedirectResponse
    {
        $priceList->update(['is_active' => ! $priceList->is_active]);

        return redirect()->route('admin.price-lists.index')->with('status', "Status price list \"{$priceList->label}\" diperbarui.");
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return Category::query()->orderBy('sort_order')->orderBy('name')->get();
    }
}
