<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageSubscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'package_id',
        'starts_at',
        'ends_at',
        'status',
        'price_snapshot',
        'discounted_price_snapshot',
        'final_price_snapshot',
        'total_washes_snapshot',
        'remaining_washes',
        'purchased_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'purchased_at' => 'datetime',
        'price_snapshot' => 'decimal:2',
        'discounted_price_snapshot' => 'decimal:2',
        'final_price_snapshot' => 'decimal:2',
        'total_washes_snapshot' => 'integer',
        'remaining_washes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active')
            ->whereDate('ends_at', '>=', now()->toDateString());
    }

    public function getIsCurrentlyActiveAttribute(): bool
    {
        if ($this->status !== 'active' || !$this->ends_at) {
            return false;
        }

        return $this->ends_at->endOfDay()->gte(now());
    }

    public function scopeActiveWithRemaining($q)
    {
        return $q->where('status', 'active')
            ->where('ends_at', '>=', now())
            ->where('remaining_washes', '>', 0);
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?? auth()->id();
                $model->updated_by = $model->updated_by ?? auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}