@php
    /** @var \App\Models\User|null $user */

    $rawMobile = $user?->mobile;

    // تنظيف الرقم من أي رموز غير أرقام
    $cleanMobile = $rawMobile ? preg_replace('/\D+/', '', $rawMobile) : null;

    $displayMobile = null;   // الصيغة المعروضة للمستخدم
    $waMobile      = null;   // الصيغة المستخدمة في رابط الواتساب (بدون +)

    if ($cleanMobile) {
        if (substr($cleanMobile, 0, 1) === '0') {
            // مثال: 0599XXXXXX → +972599XXXXXX
            $displayMobile = '+972' . substr($cleanMobile, 1);
            $waMobile      = '972' . substr($cleanMobile, 1);
        } else {
            // لو الرقم أصلاً دولي
            $displayMobile = $cleanMobile[0] === '+' ? $cleanMobile : '+' . $cleanMobile;
            $waMobile      = ltrim($cleanMobile, '+');
        }
    }
@endphp

@if ($user)
    <div class="d-flex align-items-center">
        <div class="d-flex flex-column">
            {{-- الاسم --}}
            <span class="fw-bold text-gray-900">{{ $user->name }}</span>

            {{-- رقم الجوال كرابط واتساب --}}
            @if ($displayMobile && $waMobile)
                <a href="https://wa.me/{{ $waMobile }}"
                   target="_blank"
                   class="text-muted fs-7 mt-1 text-decoration-underline">
                    {{ $displayMobile }}
                </a>
            @else
                <span class="text-muted fs-7 mt-1">لا يوجد رقم جوال</span>
            @endif
        </div>
    </div>
@else
    <span class="text-muted">مستخدم غير معروف</span>
@endif