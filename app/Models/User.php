<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasMedia
{

    use SoftDeletes, Notifiable, InteractsWithMedia, HasRoles, HasApiTokens;

    protected $table = 'users';

    protected $guarded = [];

    protected $guard_name = 'web';

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'birth_date' => 'date'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_image')->singleFile();
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function packageSubscriptions()
    {
        return $this->hasMany(PackageSubscription::class, 'user_id');
    }

    public function wallet()
    {
        return $this->hasOne(\App\Models\Wallet::class);
    }

    public function pointWallet()
    {
        return $this->hasOne(\App\Models\PointWallet::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(\App\Models\PointTransaction::class);
    }

}
