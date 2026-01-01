<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id','balance','currency','is_active','created_by','updated_by'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}