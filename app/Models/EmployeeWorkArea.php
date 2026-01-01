<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWorkArea extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id','polygon','min_lat','max_lat','min_lng','max_lng','is_active','created_by','updated_by'
    ];

    protected $casts = [
        'polygon' => 'array',
        'is_active' => 'boolean',
        'min_lat' => 'decimal:7',
        'max_lat' => 'decimal:7',
        'min_lng' => 'decimal:7',
        'max_lng' => 'decimal:7',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}

