<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'is_active', 'created_by', 'updated_by'];

    protected $casts = ['is_active' => 'boolean', 'polygon' => 'array'];

    public function user() { return $this->belongsTo(User::class); }

    public function weeklyIntervals()
    {
        return $this->hasMany(EmployeeWeeklyInterval::class);
    }

    public function timeBlocks()
    {
        return $this->hasMany(EmployeeTimeBlock::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'employee_services')
            ->withPivot(['is_active'])
            ->withTimestamps();
    }

    public function workArea()
    {
        return $this->hasOne(EmployeeWorkArea::class);
    }

}
