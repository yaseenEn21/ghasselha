<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'car_id',
        'address_id',
        'service_id',
        'employee_id',
        'package_subscription_id',
        'status',
        'booking_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'service_price_snapshot',
        'service_discounted_price_snapshot',
        'service_final_price_snapshot',
        'products_subtotal_snapshot',
        'subtotal_snapshot',
        'discount_snapshot',
        'tax_snapshot',
        'total_snapshot',
        'currency',
        'confirmed_at',
        'cancelled_at',
        'cancel_reason',
        'cancel_note',
        'meta',
        'created_by',
        'updated_by',
        'zone_id',
        'time_period',
        'service_unit_price_snapshot',
        'service_discounted_price_snapshot',
        'service_final_price_snapshot',
        'service_charge_amount_snapshot',
        'service_pricing_source',
        'service_pricing_meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'booking_date' => 'date:Y-m-d',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'service_pricing_meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function packageSubscription()
    {
        return $this->belongsTo(PackageSubscription::class, 'package_subscription_id');
    }

    public function products()
    {
        return $this->hasMany(BookingProduct::class);
    }

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function latestUnpaidInvoice()
    {
        return $this->invoices()->where('status', 'unpaid')->orderByDesc('id');
    }

    public function statusLogs()
    {
        return $this->hasMany(BookingStatusLog::class);
    }
}