<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpCode;
use Carbon\Carbon;

class OtpService
{
    public function sendLoginOtp(User $user): OtpCode
    {
        $code = random_int(1000, 9999);

        OtpCode::where('user_id', $user->id)
            ->where('type', 'login')
            ->delete();

        $otp = OtpCode::create([
            'user_id'    => $user->id,
            'mobile'     => $user->mobile,
            'code'       => (string) $code,
            'type'       => 'login',
            'expires_at' => Carbon::now()->addMinutes(1),
        ]);

        $this->sendSms($user->mobile, "رمز التحقق للدخول إلى Ghasselha هو: {$code} (صالح لمدة 5 دقائق).");

        return $otp;
    }

    /**
     * التحقق من الكود
     */
    public function verifyLoginOtp(User $user, string $code): bool
    {
        $otp = OtpCode::where('user_id', $user->id)
            ->where('type', 'login')
            ->latest()
            ->first();

        if (! $otp) {
            return false;
        }

        $otp->increment('attempts');

        if ($otp->is_used || $otp->expires_at->isPast()) {
            return false;
        }

        if ($otp->code !== $code) {
            return false;
        }

        $otp->is_used = true;
        $otp->save();

        return true;
    }

    /**
     * Msegat
     */
    protected function sendSms(string $mobile, string $message): void
    {
        \Log::info("Send OTP SMS to {$mobile}: {$message}");
    }
}