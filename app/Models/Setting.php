<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'key','value','created_by','updated_by',
    ];

    public static function getValue(string $key, $default = null): mixed
    {
        $row = static::query()->where('key', $key)->first();
        return $row?->value ?? $default;
    }
}