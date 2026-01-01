<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionCoupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'promotion_id','code','is_active',
        'starts_at','ends_at',
        'usage_limit_total','usage_limit_per_user','used_count',
        'min_invoice_total','max_discount','meta',
        'created_by','updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'meta' => 'array',
        'min_invoice_total' => 'decimal:2',
        'max_discount' => 'decimal:2',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function redemptions()
    {
        return $this->hasMany(PromotionCouponRedemption::class);
    }
}
