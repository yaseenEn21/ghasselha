<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     * يرجّع بيانات المستخدم الحالي (حسب التوكن)
     */
    public function show(Request $request)
    {
        $user = $request->user();

        $profileImageUrl = $user->getFirstMediaUrl('profile_image');

        if (!$profileImageUrl) {
            $profileImageUrl = asset('assets/media/avatars/user.png');
        }
        return api_success(new CustomerResource($user), 'profile data loaded successfully');
    }

    /**
     * POST /api/profile
     * تعديل بيانات المستخدم الحالي + صورة البروفايل
     *
     * يدعم multipart/form-data:
     * - name
     * - mobile
     * - email
     * - profile_image (ملف صورة اختياري)
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            // ✅ يبدأ بـ 05 وطوله 10 أرقام بالضبط
            'mobile' => [
                'required',
                'regex:/^05[0-9]{8}$/',
                Rule::unique('users', 'mobile')->ignore($user->id),
            ],

            // ✅ تاريخ الميلاد
            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            // ✅ الجنس
            'gender' => [
                'nullable',
                Rule::in(['male', 'female']), // أو أضف 'other' لو حاب
            ],

            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                // 'max:2048',
            ],
        ], [
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'birth_date.date' => 'تاريخ الميلاد غير صالح.',
            'birth_date.before_or_equal' => 'تاريخ الميلاد يجب أن يكون اليوم أو قبله.',
            'gender.in' => 'قيمة الجنس غير صحيحة.',
        ]);


        // تحديث البيانات الأساسية
        $user->update([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'gender' => $data['gender'] ?? 'male',
        ]);

        // حفظ / استبدال صورة البروفايل
        if ($request->hasFile('profile_image')) {
            // لو عامل singleFile في الكلكشن يكفي addMediaFromRequest
            $user->clearMediaCollection('profile_image');

            $user
                ->addMediaFromRequest('profile_image')
                ->toMediaCollection('profile_image');
        }

        return api_success(new CustomerResource($user), 'profile data updated successfully');

    }

    public function deleteProfileImage(Request $request)
    {
        $user = $request->user();

        // حذف كل الصور في كوليكشن profile_image
        $user->clearMediaCollection('profile_image');

        // إرجاع رابط الصورة الافتراضية
        $defaultUrl = [
            'profile_image_url' => asset('assets/media/avatars/user.png')
        ];

        return api_success($defaultUrl, 'تم حذف صورة البروفايل بنجاح.');

    }

}