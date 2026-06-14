<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_id', 'variant_name', 'sku', 'barcode', 'additional_price', 'stock_quantity'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Attribute values (e.g. Red, S) this variant is composed of. */
    public function values(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_value');
    }
}
