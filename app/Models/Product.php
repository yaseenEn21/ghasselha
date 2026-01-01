<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'product_category_id',
        'name','description',
        'price','discounted_price',
        'is_active','sort_order',
        'max_qty_per_booking',
        'created_by','updated_by',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'max_qty_per_booking' => 'integer',
        'price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function getFinalPriceAttribute(): float
    {
        $p = (float) $this->price;
        $d = $this->discounted_price !== null ? (float) $this->discounted_price : null;
        return $d ?? $p;
    }
}

