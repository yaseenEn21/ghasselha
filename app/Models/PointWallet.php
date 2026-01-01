<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointWallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance_points',
        'total_earned_points',
        'total_spent_points',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
