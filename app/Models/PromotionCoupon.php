<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionCoupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'promotion_id',
        'code',
        'is_active',
        'starts_at',
        'ends_at',

        'applies_to',
        'apply_all_services',
        'apply_all_packages',

        'discount_type',
        'discount_value',
        'max_discount',

        'usage_limit_total',
        'usage_limit_per_user',
        'used_count',

        'min_invoice_total',
        'meta',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'date',
        'ends_at' => 'date',

        'apply_all_services' => 'boolean',
        'apply_all_packages' => 'boolean',

        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_invoice_total' => 'decimal:2',

        'meta' => 'array',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function redemptions()
    {
        return $this->hasMany(PromotionCouponRedemption::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'promotion_coupon_services', 'promotion_coupon_id', 'service_id')
            ->withTimestamps();
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'promotion_coupon_packages', 'promotion_coupon_id', 'package_id')
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?? auth()->id();
                $model->updated_by = $model->updated_by ?? auth()->id();
            }
            $model->code = strtoupper(trim((string)$model->code));
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            if ($model->isDirty('code')) {
                $model->code = strtoupper(trim((string)$model->code));
            }
        });
    }
}