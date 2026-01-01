<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AdmissionInvite;

class SendAdmissionInviteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $invitId;

    public function __construct(int $invitId)
    {
        $this->invitId = $invitId;
    }

    public function handle(SmsService $sms): void
    {
        /** @var AdmissionInvite|null $sub */
        $invite = AdmissionInvite::find($this->invitId);

        if (!$invite) {
            return;
        }

        $phone = $invite->parent_mobile ?? null;

        $parentName = $invite->parent_name ?? null;
        $nurseryName = 'Ghasselha';
        $inviteUrl = route('admission.invite.open', $invite->token);
        $appUrl = 'https://example.com/app';

        // سطر التحية (مع اسم ولي الأمر لو موجود)
        $greeting = $parentName
            ? "السيد/ة {$parentName}، وليّ الأمر الكريم،\n\n"
            : "وليّ الأمر الكريم،\n\n";

        $message =
            $greeting .
            "نحيطكم علماً بأنه تم إنشاء دعوة التحاق لطفلكم بحضانة {$nurseryName}.\n" .
            "يرجى التكرّم باستكمال بيانات طلب الالتحاق من خلال الرابط التالي:\n" .
            "[{$inviteUrl}]\n\n" .
            "كما ندعوكم لتحميل تطبيق الحضانة لمتابعة حالة الطلب والاطلاع على مستجدات طفلكم اليومية عبر الرابط التالي:\n" .
            "[{$appUrl}]\n\n" .
            "مع خالص التحية،\n" .
            "حضانة {$nurseryName}";

        $sms->send(
            $phone,
            $message,
            sender: 'Ghasselha',
            type: 0
        );

        \Log::info('TEST V2');

        $invite->status = 'sent';
        $invite->send_at = now();
        $invite->save();
    }
}