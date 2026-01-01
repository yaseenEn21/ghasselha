<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'booking_id','from_status','to_status','note','created_by','created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function booking() { return $this->belongsTo(Booking::class); }
}