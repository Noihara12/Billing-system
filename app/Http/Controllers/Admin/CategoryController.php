<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount(['units', 'priceLists'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category(['sort_order' => 0]),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create(['sort_order' => $request->integer('sort_order')] + $request->validated());

        return redirect()->route('admin.categories.index')->with('status', "Kategori \"{$category->name}\" berhasil ditambahkan.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update(['sort_order' => $request->integer('sort_order')] + $request->validated());

        return redirect()->route('admin.categories.index')->with('status', "Kategori \"{$category->name}\" berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->units()->exists() || $category->priceLists()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', "Kategori \"{$category->name}\" masih dipakai oleh unit atau price list, tidak dapat dihapus.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', "Kategori \"{$category->name}\" berhasil dihapus.");
    }
}
