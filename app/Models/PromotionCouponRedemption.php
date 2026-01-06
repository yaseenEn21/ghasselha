<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionCouponRedemption extends Model
{
    protected $fillable = [
        'promotion_coupon_id',
        'user_id',
        'invoice_id',
        'discount_amount',
        'status',
        'applied_at',
        'voided_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'applied_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(PromotionCoupon::class, 'promotion_coupon_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
