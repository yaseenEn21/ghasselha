<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PointTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id','type','points',
        'money_amount','currency',
        'reference_type','reference_id',
        'note','meta',
        'is_archived','archived_at',
        'created_by','updated_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
