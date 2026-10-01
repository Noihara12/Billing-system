<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCarouselRequest;
use App\Http\Requests\Admin\UpdateCarouselRequest;
use App\Models\Carousel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CarouselController extends Controller
{
    public function index(): View
    {
        $carousels = Carousel::query()
            ->orderBy('sort_order')
            ->paginate(15);

        return view('admin.carousels.index', compact('carousels'));
    }

    public function create(): View
    {
        return view('admin.carousels.create', [
            'carousel' => new Carousel(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(StoreCarouselRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('carousel', 'public');
            $data['image_path'] = $path;
        }

        $carousel = Carousel::create($data + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('admin.carousels.index')->with('status', "Carousel \"{$carousel->title}\" berhasil ditambahkan.");
    }

    public function edit(Carousel $carousel): View
    {
        return view('admin.carousels.edit', compact('carousel'));
    }

    public function update(UpdateCarouselRequest $request, Carousel $carousel): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($carousel->image_path && Storage::disk('public')->exists($carousel->image_path)) {
                Storage::disk('public')->delete($carousel->image_path);
            }
            $path = $request->file('image')->store('carousel', 'public');
            $data['image_path'] = $path;
        }

        $carousel->update($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.carousels.index')->with('status', "Carousel \"{$carousel->title}\" berhasil diperbarui.");
    }

    public function destroy(Carousel $carousel): RedirectResponse
    {
        if ($carousel->image_path && Storage::disk('public')->exists($carousel->image_path)) {
            Storage::disk('public')->delete($carousel->image_path);
        }

        $carousel->delete();

        return redirect()->route('admin.carousels.index')->with('status', "Carousel \"{$carousel->title}\" berhasil dihapus.");
    }

    public function toggle(Carousel $carousel): RedirectResponse
    {
        $carousel->update(['is_active' => ! $carousel->is_active]);

        return redirect()->route('admin.carousels.index')->with('status', "Status carousel \"{$carousel->title}\" diperbarui.");
    }
}
