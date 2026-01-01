<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\PointWallet;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;

class PointsService
{
    public function getRedeemRule(): array
    {
        $points = (int) Setting::getValue('points.redeem_points', 1450);
        $amount = (float) Setting::getValue('points.redeem_amount', 72.50);
        $min = (int) Setting::getValue('points.min_redeem_points', 100);

        $points = max(1, $points);
        $amount = max(0, $amount);
        $min = max(0, $min);

        return [
            'redeem_points' => $points,
            'redeem_amount' => round($amount, 2),
            'min_redeem_points' => $min,
            'currency' => 'SAR',
        ];
    }

    public function ensureWallet(User $user): PointWallet
    {
        return PointWallet::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance_points' => 0, 'total_earned_points' => 0, 'total_spent_points' => 0]
        );
    }

    public function balanceValue(int $balancePoints): float
    {
        $rule = $this->getRedeemRule();
        if ($rule['redeem_points'] <= 0)
            return 0;

        return round(($balancePoints / $rule['redeem_points']) * $rule['redeem_amount'], 2);
    }

    public function previewRedeem(User $user, ?int $pointsToRedeem = null): array
    {
        $rule = $this->getRedeemRule();
        $wallet = $this->ensureWallet($user);

        $balance = (int) $wallet->balance_points;
        $points = $pointsToRedeem ?? $balance;
        $points = (int) $points;

        $canRedeem = true;
        $reasons = [];

        if ($points <= 0) {
            $canRedeem = false;
            $reasons[] = 'invalid_points';
        }

        if ($points < (int) $rule['min_redeem_points']) {
            $canRedeem = false;
            $reasons[] = 'min_not_met';
        }

        if ($points > $balance) {
            $canRedeem = false;
            $reasons[] = 'insufficient_points';
        }

        $money = 0;
        if ($canRedeem) {
            $money = round(($points / $rule['redeem_points']) * $rule['redeem_amount'], 2);
            if ($money <= 0) {
                $canRedeem = false;
                $reasons[] = 'invalid_rule';
            }
        }

        return [
            'can_redeem' => $canRedeem,
            'reasons' => $reasons,

            'requested_points' => $pointsToRedeem,
            'points_to_redeem' => $points,

            'balance_points' => $balance,

            'money_amount' => (string) $money,
            'currency' => $rule['currency'],

            'redeem_rule' => [
                'points' => (int) $rule['redeem_points'],
                'amount' => (string) $rule['redeem_amount'],
                'currency' => $rule['currency'],
                'min_redeem_points' => (int) $rule['min_redeem_points'],
            ],
        ];
    }
    /**
     * إضافة نقاط (مثلاً بعد اكتمال الحجز)
     */
    public function earn(User $user, int $points, ?string $referenceType = null, ?int $referenceId = null, ?string $note = null, ?int $actorId = null): PointTransaction
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be > 0');
        }

        return DB::transaction(function () use ($user, $points, $referenceType, $referenceId, $note, $actorId) {
            $wallet = PointWallet::query()->where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet)
                $wallet = $this->ensureWallet($user)->fresh();

            $wallet->balance_points += $points;
            $wallet->total_earned_points += $points;
            $wallet->save();

            return PointTransaction::create([
                'user_id' => $user->id,
                'type' => 'earn',
                'points' => $points,
                'money_amount' => null,
                'currency' => 'SAR',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        });
    }

    /**
     * تحويل نقاط إلى رصيد محفظة (افتراضي: محفظة)
     */
    public function redeemToWallet(User $user, ?int $pointsToRedeem = null, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($user, $pointsToRedeem, $actorId) {

            $rule = $this->getRedeemRule();

            $wallet = PointWallet::query()->where('user_id', $user->id)->lockForUpdate()->first();
            if (!$wallet)
                $wallet = $this->ensureWallet($user)->fresh();

            $balance = (int) $wallet->balance_points;

            $points = $pointsToRedeem ?? $balance; // default: redeem all
            $points = (int) $points;

            if ($points <= 0) {
                return ['ok' => false, 'message' => 'Invalid points', 'errors' => ['points' => ['Invalid points']]];
            }

            if ($points < $rule['min_redeem_points']) {
                return ['ok' => false, 'message' => 'Minimum redeem points not met', 'errors' => ['points' => ['Minimum redeem points not met']]];
            }

            if ($points > $balance) {
                return ['ok' => false, 'message' => 'Insufficient points', 'errors' => ['points' => ['Insufficient points']]];
            }

            // قيمة التحويل
            $money = round(($points / $rule['redeem_points']) * $rule['redeem_amount'], 2);
            if ($money <= 0) {
                return ['ok' => false, 'message' => 'Redeem rule invalid', 'errors' => ['settings' => ['Redeem rule invalid']]];
            }

            // خصم النقاط
            $wallet->balance_points -= $points;
            $wallet->total_spent_points += $points;
            $wallet->save();

            $tx = PointTransaction::create([
                'user_id' => $user->id,
                'type' => 'redeem',
                'points' => -1 * $points,
                'money_amount' => $money,
                'currency' => $rule['currency'],
                'note' => 'Redeem points to wallet',
                'meta' => [
                    'redeem_rule' => $rule,
                ],
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            // ✅ إضافة لمحفظة الرصيد (عدّل أسماء الجداول لو مختلفة عندك)
            $this->creditUserWallet($user->id, $money, $actorId, [
                'source' => 'points',
                'point_transaction_id' => $tx->id,
            ]);

            return [
                'ok' => true,
                'wallet' => $wallet->fresh(),
                'transaction' => $tx,
                'money_amount' => $money,
                'currency' => $rule['currency'],
            ];
        });
    }

    /**
     * دمج مع نظام المحفظة اللي عندك
     * - لو عندك WalletService استبدل هذا بمكالمة للخدمة
     */
    private function creditUserWallet(int $userId, float $amount, ?int $actorId = null, array $meta = []): void
    {
        DB::transaction(function () use ($userId, $amount, $actorId, $meta) {

            // 1) اجلب/أنشئ المحفظة + lock
            $wallet = Wallet::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $userId,
                    'balance' => 0,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);
                // مهم: خليه locked ضمن الترانزاكشن
                $wallet->refresh();
            }

            $before = (float) $wallet->balance;
            $after = $before + (float) $amount;

            // 2) تحديث رصيد المحفظة
            $wallet->update([
                'balance' => $after,
                'updated_by' => $actorId,
            ]);

            // 3) سجل حركة محفظة (حسب enums عندك)
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $userId,

                // ✅ مهم جدا حسب جدولك
                'direction' => 'credit',
                'type' => 'adjustment', // أو refund / topup .. إلخ
                // أنا اخترت adjustment لأنه "رصيد ناتج عن استبدال النقاط"
                // لو بتحب نعمل enum جديد: points_redeem (أفضل) قولي.

                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,

                'description' => [
                    'ar' => 'رصيد ناتج عن استبدال النقاط',
                    'en' => 'Credit from points redeem',
                ],
                'meta' => $meta,

                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        });
    }

}