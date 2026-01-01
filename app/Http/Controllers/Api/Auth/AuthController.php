<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CustomerResource;
use App\Models\User;
use App\Services\OtpService;
use Hash;
use Illuminate\Http\Request;
use App\Models\OtpCode;

class AuthController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'mobile' => ['required', 'digits:10', 'starts_with:05'],
        ]);

        $user = User::where('mobile', $data['mobile'])
            ->where('is_active', true)
            ->first();

        if (!$user) {
            $user = User::create([
                'mobile' => $data['mobile'],
                'user_type' => 'customer',
                'is_active' => true,
                'password' => Hash::make('123456'),
                'name' => 'عميل جديد',
            ]);
            
            $otp = $this->otpService->sendLoginOtp($user);

            \Log::info('New User created', ['mobile' => $user->mobile, 'otp' => $otp->code]);

            return response()->json([
                'message' => 'تم إرسال رمز التحقق إلى رقم الجوال.',
                'data' => [
                    'mobile' => $user->mobile,
                    'otp' => $otp->code,
                ],
            ]);
        }

        $existing = OtpCode::where('user_id', $user->id)
            ->where('type', 'login')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($existing) {
            $secondsRemaining = now()->diffInSeconds($existing->expires_at, false);
            $minutes = ceil($secondsRemaining / 60);

            return response()->json([
                'message' => 'تم إرسال رمز تحقق من قبل، يرجى الانتظار قبل طلب رمز جديد.',
                'data' => [
                    'seconds_remaining' => $secondsRemaining,
                    'minutes_remaining' => $minutes,
                ],
            ], 429);
        }

        $otp = $this->otpService->sendLoginOtp($user);

        return response()->json([
            'message' => 'تم إرسال رمز التحقق إلى رقم الجوال.',
            'data' => [
                'mobile' => $user->mobile,
                'otp' => $otp->code,
            ],
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'mobile' => ['required', 'string'],
            'code' => ['required', 'string', 'size:4'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::where('mobile', $data['mobile'])
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'لا يوجد مستخدم مسجل بهذا الرقم.',
            ], 404);
        }

        $isValid = $this->otpService->verifyLoginOtp($user, $data['code']);

        if (!$isValid) {
            return response()->json([
                'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
            ], 422);
        }

        $device_name = $request->device_name ?? 'mobile-app';
        $token = $user->createToken($device_name)->plainTextToken;

        $customer = new CustomerResource($user);

        return api_success([
            'token' => $token,
            'user' => $customer,
        ], 'تم تسجيل الدخول بنجاح.');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }

    public function logoutAll(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'تم تسجيل خروجك من جميع الأجهزة بنجاح.',
        ]);
    }

}