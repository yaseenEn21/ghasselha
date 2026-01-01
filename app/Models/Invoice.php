<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number',
        'user_id',
        'invoiceable_type',
        'invoiceable_id',
        'type',
        'parent_invoice_id',
        'version',
        'status',
        'subtotal',
        'discount',
        'tax',
        'total',
        'currency',
        'issued_at',
        'paid_at',
        'is_locked',
        'meta',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'is_locked' => 'boolean',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function parent()
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    public function children()
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    } // لو payments جدولك موجود

    public function resolvePayableType(): string
    {
        // لو أنت بتخزن purpose داخل meta (اختياري)
        // $purpose = $this->meta['purpose'] ?? null;
        // if (is_string($purpose) && $purpose !== '') {
        //     return $purpose;
        // }

        // // لو الفاتورة adjustment/credit_note، غالباً ترجع لنفس غرض الأب
        // if (in_array($this->type, ['adjustment', 'credit_note'], true) && $this->parent_invoice_id) {
        //     $parent = self::query()->select(['id', 'invoiceable_type', 'meta', 'type', 'parent_invoice_id'])
        //         ->find($this->parent_invoice_id);

        //     if ($parent) {
        //         return $parent->resolvePayableType();
        //     }
        // }

        // Mapping حسب invoiceable_type
        return match ($this->invoiceable_type) {
            \App\Models\Booking::class => 'booking_payment',

            \App\Models\Package::class => 'package_purchase',

            \App\Models\PackageSubscription::class => 'package_purchase',

            default => 'invoice_payment',
        };
    }

    public function resolvePayableId(): ?int
    {
        return $this->invoiceable_id ? (int) $this->invoiceable_id : null;
    }
}
