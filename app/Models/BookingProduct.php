<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingProduct extends Model
{
    protected $fillable = [
        'booking_id','product_id','qty','unit_price_snapshot','title','line_total',
    ];

    protected $casts = [
        'title' => 'array',
    ];

    public function booking() { return $this->belongsTo(Booking::class); }
    public function product() { return $this->belongsTo(Product::class); }
}