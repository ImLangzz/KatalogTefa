<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->with(['seller', 'images'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', compact('products', 'search'));
    }

    public function show(Product $product): View
    {
        $product->load(['seller', 'images']);

        return view('catalog.show', compact('product'));
    }

    public function create(): View
    {
        abort_if(auth()->user()->is_admin, 403);

        return view('catalog.form', ['product' => new Product]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->is_admin, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['required', 'string', 'max:80'],
            'price' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'location_address' => ['nullable', 'string', 'max:1000'],
            'location_latitude' => ['nullable', 'required_with:location_longitude', 'numeric', 'between:-90,90'],
            'location_longitude' => ['nullable', 'required_with:location_latitude', 'numeric', 'between:-180,180'],
            'images' => ['required', 'array', 'min:1', 'max:8'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $product = $request->user()->products()->create(collect($validated)->except('images')->all());
        $this->storeImages($product, $request->file('images'));

        return redirect()->route('catalog.show', $product)->with('success', 'Katalog berhasil dipublikasikan.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeOwner($product);
        $product->load('images');

        return view('catalog.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeOwner($product);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['required', 'string', 'max:80'],
            'price' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'location_address' => ['nullable', 'string', 'max:1000'],
            'location_latitude' => ['nullable', 'required_with:location_longitude', 'numeric', 'between:-90,90'],
            'location_longitude' => ['nullable', 'required_with:location_latitude', 'numeric', 'between:-180,180'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:product_images,id'],
        ]);

        $deleteIds = $validated['delete_images'] ?? [];
        $remainingCount = $product->images()->whereNotIn('id', $deleteIds)->count();
        $newImages = $request->file('images', []);

        if ($remainingCount + count($newImages) < 1) {
            return back()->withErrors(['images' => 'Katalog harus memiliki setidaknya satu foto.'])->withInput();
        }

        if ($remainingCount + count($newImages) > 8) {
            return back()->withErrors(['images' => 'Katalog maksimal memiliki 8 foto. Hapus foto lama sebelum menambah foto baru.'])->withInput();
        }

        $product->update(collect($validated)->except(['images', 'delete_images'])->all());

        foreach ($product->images()->whereIn('id', $deleteIds)->get() as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        if ($newImages !== []) {
            $this->storeImages($product, $newImages);
        }

        return redirect()->route('catalog.show', $product)->with('success', 'Katalog berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeOwner($product);
        $this->deleteProductImages($product);
        $product->delete();

        return redirect()->route('catalog.index')->with('success', 'Katalog berhasil dihapus.');
    }

    private function authorizeOwner(Product $product): void
    {
        abort_unless(! auth()->user()->is_admin && auth()->id() === $product->user_id, 403);
    }

    private function storeImages(Product $product, array $images): void
    {
        $nextOrder = (int) $product->images()->max('sort_order') + 1;

        foreach ($images as $image) {
            $path = $image->store('catalogs', 'public');

            if ($path === false) {
                throw new \RuntimeException('Foto katalog gagal disimpan.');
            }

            $product->images()->create([
                'path' => $path,
                'sort_order' => $nextOrder++,
            ]);
        }
    }

    private function deleteProductImages(Product $product): void
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
    }
}
