<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleModel extends Model
{
    use SoftDeletes;

    protected $fillable = ['vehicle_make_id', 'external_id', 'name', 'is_active', 'sort_order'];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function make()
    {
        return $this->belongsTo(VehicleMake::class, 'vehicle_make_id');
    }
}
