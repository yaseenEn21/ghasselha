<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleMake extends Model
{
    use SoftDeletes;

    protected $fillable = ['external_id', 'name', 'is_active', 'sort_order'];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function models()
    {
        return $this->hasMany(VehicleModel::class, 'vehicle_make_id');
    }
}
