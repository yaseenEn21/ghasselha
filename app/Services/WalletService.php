<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function getOrCreateWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'SAR', 'is_active' => true]
        );
    }

    public function credit(
        User $user,
        float $amount,
        string $type = 'topup',
        ?array $description = null,
        ?Model $referenceable = null,
        ?int $paymentId = null,
        ?int $actorId = null,
        ?array $meta = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be > 0');
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceable, $paymentId, $actorId, $meta) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet) {
                $wallet = $this->getOrCreateWallet($user);
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            }

            $before = (float) $wallet->balance;
            $after  = $before + $amount;

            $wallet->update([
                'balance' => $after,
                'updated_by' => $actorId,
            ]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'direction' => 'credit',
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => $description,
                'meta' => $meta,
                'referenceable_type' => $referenceable ? get_class($referenceable) : null,
                'referenceable_id' => $referenceable?->getKey(),
                'payment_id' => $paymentId,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        });
    }

    public function debit(
        User $user,
        float $amount,
        string $type = 'booking_charge',
        ?array $description = null,
        ?Model $referenceable = null,
        ?int $paymentId = null,
        ?int $actorId = null,
        ?array $meta = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be > 0');
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $referenceable, $paymentId, $actorId, $meta) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet) {
                $wallet = $this->getOrCreateWallet($user);
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            }

            $before = (float) $wallet->balance;

            if ($before < $amount) {
                throw new \RuntimeException('Insufficient wallet balance');
            }

            $after = $before - $amount;

            $wallet->update([
                'balance' => $after,
                'updated_by' => $actorId,
            ]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'direction' => 'debit',
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => $description,
                'meta' => $meta,
                'referenceable_type' => $referenceable ? get_class($referenceable) : null,
                'referenceable_id' => $referenceable?->getKey(),
                'payment_id' => $paymentId,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        });
    }
}