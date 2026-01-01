<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_type' => $this->item_type,

            'title' => $this->title ? i18n($this->title) : null,
            'description' => $this->description ? i18n($this->description) : null,

            'qty' => (string) $this->qty,
            'unit_price' => (string) $this->unit_price,
            'line_tax' => (string) $this->line_tax,
            'line_total' => (string) $this->line_total,
        ];
    }
}