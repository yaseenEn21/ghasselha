<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id','vehicle_make_id','vehicle_model_id',
        'color','plate_number','plate_letters','plate_letters_ar',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()  { return $this->belongsTo(User::class); }
    public function make()  { return $this->belongsTo(VehicleMake::class, 'vehicle_make_id'); }
    public function model() { return $this->belongsTo(VehicleModel::class, 'vehicle_model_id'); }

    public function getPlateFullAttribute(): string
    {
        return trim($this->plate_letters.'-'.$this->plate_number);
    }
}
