<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'payable_type',
        'payable_id',
        'amount',
        'currency',
        'method',
        'status',
        'gateway',
        'gateway_payment_id',
        'gateway_status',
        'gateway_transaction_url',
        'gateway_raw',
        'paid_at',
        'meta',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'meta' => 'array',
        'gateway_raw' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function payable()
    {
        return $this->morphTo();
    }
}
