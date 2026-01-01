<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWeeklyInterval extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id','day','type','start_time','end_time','is_active','created_by','updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeWork($q) { return $q->where('type', 'work'); }
    public function scopeBreak($q) { return $q->where('type', 'break'); }
}
