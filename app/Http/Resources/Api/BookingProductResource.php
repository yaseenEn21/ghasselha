<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id' => (int) $this->product_id,
            'qty' => (int) $this->qty,
            'unit_price' => (float) $this->unit_price_snapshot,
            'title' => $this->title,
            'title_i18n' => $this->title ? i18n($this->title) : null,
            'line_total' => (float) $this->line_total,
            
            'product' => $this->relationLoaded('product') && $this->product
                ? new ProductResource($this->product)
                : null,

        ];
    }
}