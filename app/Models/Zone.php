<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'polygon',
        'min_lat',
        'max_lat',
        'min_lng',
        'max_lng',
        'center_lat',
        'center_lng',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'polygon' => 'array',
        'min_lat' => 'decimal:7',
        'max_lat' => 'decimal:7',
        'min_lng' => 'decimal:7',
        'max_lng' => 'decimal:7',
        'center_lat' => 'decimal:7',
        'center_lng' => 'decimal:7',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function servicePrices()
    {
        return $this->hasMany(ServiceZonePrice::class, 'zone_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
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