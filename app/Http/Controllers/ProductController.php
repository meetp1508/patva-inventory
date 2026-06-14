<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->q;
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhere('barcode', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('name')->get();

        return view('products.create', compact('categories', 'attributes'));
    }

    public function store(StoreProductRequest $request)
    {
        $this->products->create(
            $request->safe()->except(['images', 'deleted_images', 'variants']),
            $request->file('images', []),
            $this->variantPayload($request),
        );

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'variants', 'images');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('name')->get();
        $product->load('variants.values');

        return view('products.edit', compact('product', 'categories', 'attributes'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->products->update(
            $product,
            $request->safe()->except(['images', 'deleted_images', 'variants']),
            $request->file('images', []),
            $request->input('deleted_images', []),
            $this->variantPayload($request),
        );

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * The variant rows from the builder, or null when the builder wasn't part of
     * the submission (so variants are left untouched).
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function variantPayload(Request $request): ?array
    {
        if (! $request->boolean('variant_builder')) {
            return null;
        }

        return $request->input('variants', []);
    }

    public function destroy(Product $product)
    {
        $this->products->delete($product);

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
