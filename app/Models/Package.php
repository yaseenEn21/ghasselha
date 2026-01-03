<?php

// app/Models/Package.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Package extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'price',
        'discounted_price',
        'validity_days',
        'sort_order',
        'is_active',
        'washes_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'validity_days' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'washes_count' => 'integer',
        'name' => 'array',
        'description' => 'array',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'package_service', 'package_id', 'service_id')
            ->withPivot(['sort_order'])
            ->withTimestamps()
            ->orderBy('package_service.sort_order');
    }

    public function subscriptions()
    {
        return $this->hasMany(PackageSubscription::class, 'package_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function getImageUrl(): ?string
    {
        $url = $this->getFirstMediaUrl('image');
        return $url ?: null;
    }

    public function getNameForLocale(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return $this->name[$locale] ?? $this->name['ar'] ?? $this->name['en'] ?? '—';
    }

    public function getDescriptionForLocale(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return $this->description[$locale] ?? $this->description['ar'] ?? $this->description['en'] ?? '';
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