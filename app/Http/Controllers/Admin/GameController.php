<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGameRequest;
use App\Http\Requests\Admin\UpdateGameRequest;
use App\Models\Game;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(Request $request): View
    {
        $games = Game::query()
            ->withCount('units')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->string('status') === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.games.index', compact('games'));
    }

    public function create(): View
    {
        return view('admin.games.create', [
            'game' => new Game(['is_active' => true]),
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreGameRequest $request): RedirectResponse
    {
        $game = Game::create($request->safe()->except('unit_ids') + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        $game->units()->sync($request->input('unit_ids', []));

        return redirect()->route('admin.games.index')->with('status', "Game {$game->name} berhasil ditambahkan.");
    }

    public function edit(Game $game): View
    {
        $game->load('units');

        return view('admin.games.edit', [
            'game' => $game,
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateGameRequest $request, Game $game): RedirectResponse
    {
        $game->update($request->safe()->except('unit_ids') + [
            'is_active' => $request->boolean('is_active'),
        ]);

        $game->units()->sync($request->input('unit_ids', []));

        return redirect()->route('admin.games.index')->with('status', "Game {$game->name} berhasil diperbarui.");
    }

    public function destroy(Game $game): RedirectResponse
    {
        $game->delete();

        return redirect()->route('admin.games.index')->with('status', "Game {$game->name} berhasil dihapus.");
    }
}
