<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = (float) $this->price;
        $disc  = $this->discounted_price !== null ? (float) $this->discounted_price : null;

        return [
            'id' => $this->id,
            'product_category_id' => $this->product_category_id,

            'name' => i18n($this->name),
            'description' => i18n($this->description),

            'price' => number_format($price, 2, '.', ''),
            'discounted_price' => $disc !== null ? number_format($disc, 2, '.', '') : null,

            'max_qty_per_booking' => $this->max_qty_per_booking,

            'image_url' => $this->getFirstMediaUrl('image') ?: defaultImage('product.png'),
        ];
    }
}
