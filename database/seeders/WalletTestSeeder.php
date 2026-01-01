<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WalletTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $actorId = DB::table('users')->where('user_type', 'admin')->value('id')
                ?? DB::table('users')->orderBy('id')->value('id');

            $users = User::query()->whereIn('id', [4,5])->get();
            if ($users->count() === 0) return;

            $walletService = app(WalletService::class);

            foreach ($users as $u) {

                // Payment 1 (ApplePay)
                $p1 = Payment::create([
                    'user_id' => $u->id,
                    'amount' => 70,
                    'currency' => 'SAR',
                    'method' => 'apple_pay',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);

                $walletService->credit(
                    $u,
                    70,
                    'topup',
                    ['ar' => 'إضافة رصيد للمحفظة', 'en' => 'Wallet top up'],
                    null,
                    $p1->id,
                    $actorId,
                    ['source' => 'seed']
                );

                // Payment 2 (Credit Card)
                $p2 = Payment::create([
                    'user_id' => $u->id,
                    'amount' => 70,
                    'currency' => 'SAR',
                    'method' => 'credit_card',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);

                $walletService->credit(
                    $u,
                    70,
                    'topup',
                    ['ar' => 'إضافة رصيد للمحفظة', 'en' => 'Wallet top up'],
                    null,
                    $p2->id,
                    $actorId
                );

                // خصم تجريبي (حجز)
                $walletService->debit(
                    $u,
                    30,
                    'booking_charge',
                    ['ar' => 'خصم مقابل حجز', 'en' => 'Booking charge'],
                    null,
                    null,
                    $actorId
                );

                // Refund
                $walletService->credit(
                    $u,
                    15,
                    'refund',
                    ['ar' => 'استرجاع إلى المحفظة', 'en' => 'Refund to wallet'],
                    null,
                    null,
                    $actorId
                );
            }
        });
    }
}