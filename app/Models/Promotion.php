<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','description',
        'applies_to','apply_all_services','apply_all_packages',
        'discount_type','discount_value','max_discount',
        'starts_at','ends_at','is_active',
        'created_by','updated_by',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'apply_all_services' => 'boolean',
        'apply_all_packages' => 'boolean',
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function coupons()
    {
        return $this->hasMany(PromotionCoupon::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'promotion_services')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'promotion_packages')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
