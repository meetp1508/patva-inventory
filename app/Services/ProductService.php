<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        private readonly BarcodeService $barcodes,
        private readonly VariantService $variants,
    ) {
    }

    /**
     * Create a product, storing any uploaded gallery images and auto-generating
     * a barcode when one was not supplied.
     *
     * @param array<string, mixed>            $data
     * @param array<int, UploadedFile>        $images
     * @param array<int, array<string,mixed>>|null $variants  Variant rows from the builder; null = leave variants untouched.
     */
    public function create(array $data, array $images = [], ?array $variants = null): Product
    {
        return DB::transaction(function () use ($data, $images, $variants) {
            $product = Product::create($data);

            $this->addImages($product, $images);
            $this->syncPrimaryImage($product);

            if (empty($product->barcode)) {
                $this->barcodes->assignToProduct($product);
            }

            if ($variants !== null) {
                $this->variants->sync($product, $variants);
            }

            return $product;
        });
    }

    /**
     * Update a product: remove any images the user deleted, append newly
     * uploaded ones, and keep the primary `image` column in sync.
     *
     * @param array<string, mixed>            $data
     * @param array<int, UploadedFile>        $images
     * @param array<int, int|string>          $deletedImageIds
     * @param array<int, array<string,mixed>>|null $variants  Variant rows from the builder; null = leave variants untouched.
     */
    public function update(Product $product, array $data, array $images = [], array $deletedImageIds = [], ?array $variants = null): Product
    {
        return DB::transaction(function () use ($product, $data, $images, $deletedImageIds, $variants) {
            $product->update($data);

            if (! empty($deletedImageIds)) {
                $this->removeImages($product, $deletedImageIds);
            }

            $this->addImages($product, $images);
            $this->syncPrimaryImage($product);

            if ($variants !== null) {
                $this->variants->sync($product, $variants);
            }

            return $product;
        });
    }

    public function delete(Product $product): void
    {
        // Keep the image files; soft-deletes mean the product may be restored.
        $product->delete();
    }

    /**
     * Store uploaded files and attach them to the product, appending after any
     * existing images.
     *
     * @param array<int, UploadedFile> $images
     */
    private function addImages(Product $product, array $images): void
    {
        $images = array_filter($images);

        if (empty($images)) {
            return;
        }

        $sort = (int) $product->images()->max('sort_order');

        foreach ($images as $image) {
            $product->images()->create([
                'path'       => $image->store('products', 'public'),
                'sort_order' => ++$sort,
            ]);
        }
    }

    /**
     * Delete the given images (rows + files), scoped to this product.
     *
     * @param array<int, int|string> $imageIds
     */
    private function removeImages(Product $product, array $imageIds): void
    {
        $product->images()->whereIn('id', $imageIds)->get()
            ->each(function (ProductImage $image): void {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
    }

    /**
     * Mirror the first gallery image into the legacy `image` column so list,
     * POS and detail thumbnails keep working from a single source.
     */
    private function syncPrimaryImage(Product $product): void
    {
        $primary = $product->images()->first();
        $product->forceFill(['image' => $primary?->path])->save();
    }
}
