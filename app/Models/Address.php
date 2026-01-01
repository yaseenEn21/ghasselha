<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Address extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id','type',
        'country','city','area','address_line',
        'building_name','building_number','landmark',
        'lat','lng',
        'is_default',
        'created_by','updated_by',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}