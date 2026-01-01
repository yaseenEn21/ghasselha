<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id','user_id',
        'direction','type','amount',
        'balance_before','balance_after','description','meta',
        'referenceable_type','referenceable_id',
        'payment_id',
        'created_by','updated_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'description' => 'array',
        'meta' => 'array'
    ];

    public function wallet() { return $this->belongsTo(Wallet::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function payment() { return $this->belongsTo(Payment::class); }

    public function referenceable()
    {
        return $this->morphTo();
    }
}
