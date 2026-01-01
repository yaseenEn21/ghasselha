<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTimeBlock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id','date','start_time','end_time','reason','is_active','created_by','updated_by'
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}

