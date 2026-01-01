<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id','item_type',
        'itemable_type','itemable_id',
        'title','description',
        'qty','unit_price','line_tax','line_total',
        'meta','sort_order'
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'meta' => 'array',
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'line_tax' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice() { return $this->belongsTo(Invoice::class); }

    public function itemable() { return $this->morphTo(); }
}
