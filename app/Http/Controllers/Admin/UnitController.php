<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUnitRequest;
use App\Http\Requests\Admin\UpdateUnitRequest;
use App\Models\Category;
use App\Models\Game;
use App\Models\TasmotaDevice;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $units = Unit::query()
            ->withCount('games')
            ->with(['tasmotaDevice', 'category', 'games:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search');
                $q->where(fn ($q2) => $q2->where('name', 'like', "%{$term}%")->orWhere('unit_code', 'like', "%{$term}%"));
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.units.index', [
            'units' => $units,
            'statuses' => $this->statuses(),
            'categories' => $this->categories(),
        ]);
    }

    public function create(): View
    {
        return view('admin.units.create', [
            'unit' => new Unit(['status' => Unit::STATUS_AVAILABLE, 'is_active' => true]),
            'games' => Game::query()->orderBy('name')->get(),
            'tasmotaDevices' => $this->availableTasmotaDevices(),
            'statuses' => $this->statuses(),
            'categories' => $this->categories(),
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $unit = Unit::create($request->safe()->except('game_ids') + [
            'auto_power_on' => $request->boolean('auto_power_on'),
            'auto_power_off' => $request->boolean('auto_power_off'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $unit->games()->sync($request->input('game_ids', []));

        return redirect()->route('admin.units.index')->with('status', "Unit {$unit->name} berhasil ditambahkan.");
    }

    public function edit(Unit $unit): View
    {
        $unit->load('games');

        return view('admin.units.edit', [
            'unit' => $unit,
            'games' => Game::query()->orderBy('name')->get(),
            'tasmotaDevices' => $this->availableTasmotaDevices($unit->id),
            'statuses' => $this->statuses(),
            'categories' => $this->categories(),
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->safe()->except('game_ids') + [
            'auto_power_on' => $request->boolean('auto_power_on'),
            'auto_power_off' => $request->boolean('auto_power_off'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $unit->games()->sync($request->input('game_ids', []));

        return redirect()->route('admin.units.index')->with('status', "Unit {$unit->name} berhasil diperbarui.");
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->route('admin.units.index')->with('status', "Unit {$unit->name} berhasil dihapus.");
    }

    /**
     * @return array<int, string>
     */
    private function statuses(): array
    {
        return [
            Unit::STATUS_AVAILABLE,
            Unit::STATUS_BOOKED,
            Unit::STATUS_PLAYING,
            Unit::STATUS_MAINTENANCE,
            Unit::STATUS_OFFLINE,
        ];
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return Category::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * @return Collection<int, TasmotaDevice>
     */
    private function availableTasmotaDevices(?int $exceptUnitId = null): Collection
    {
        return TasmotaDevice::query()
            ->where(function ($q) use ($exceptUnitId) {
                $q->whereDoesntHave('unit');

                if ($exceptUnitId) {
                    $q->orWhereHas('unit', fn ($q2) => $q2->where('units.id', $exceptUnitId));
                }
            })
            ->orderBy('device_name')
            ->get();
    }
}
