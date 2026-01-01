<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceReminderLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\GolanSubscription;

class InvoiceReminderService
{
    public function sendReminderForInvoice(Invoice $invoice, string $rule, \App\Services\TwilioService $twilio): bool
    {
        $runDate = Carbon::today()->toDateString();

        // منع التكرار في نفس اليوم لنفس الفاتورة + القاعدة
        $exists = InvoiceReminderLog::where('invoice_id', $invoice->id)
            ->where('rule', $rule)
            ->where('run_date', $runDate)
            ->exists();

        if ($exists) {
            Log::info('[REM] Skipped (already sent today)', [
                'invoice_id' => $invoice->id,
                'rule' => $rule,
                'run_date' => $runDate,
            ]);
            return false;
        }

        // === اختيار رقم الهدف حسب نوع الكيان ===
        $targetPhone = null;
        $targetLabel = null; // للّوج فقط
        $entity = $invoice->invoiceable; // تأكد إنك عملت eager load بالـ command (with('invoiceable'))

        if ($entity instanceof GolanSubscription) {
            // لجولان: الرقم هو رقم الخط نفسه
            $targetPhone = $this->normalizeMsisdn($entity->subscription_number);
            $targetLabel = 'golan:subscription_number';
        } else {
            // باقي الكيانات: جرّب رقم الزبون المربوط بالفاتورة، أو عبر الكيان
            $targetPhone = $this->normalizeMsisdn(
                $invoice->customer?->phone
                ?? optional($entity)->customer?->phone
            );
            $targetLabel = 'invoice/customer phone';
        }

        if (!$targetPhone) {
            InvoiceReminderLog::create([
                'invoice_id' => $invoice->id,
                'rule' => $rule,
                'run_date' => $runDate,
                'channel' => 'sms',
                'status' => 'failed',
                'response' => 'No destination phone for this invoice',
            ]);
            Log::warning('[REM] No destination phone', [
                'invoice_id' => $invoice->id,
                'resolved_from' => $targetLabel,
            ]);
            return false;
        }

        // تجهيز الرسالة
        $number = $invoice->number ?: $invoice->id;
        $due = optional($invoice->due_date)->format('Y-m-d');
        $note = trim((string) $invoice->notes);
        $noteSnip = $note !== '' ? "\nملاحظة: " . mb_strimwidth($note, 0, 120, '…', 'UTF-8') : '';

        // لو الفاتورة لجولان نضيف رقم الخط للتوضيح
        $isGolan = $entity instanceof GolanSubscription;
        $lineInfo = $isGolan ? "\nرقم الخط: " . ($entity->subscription_number ?? '—') : '';

        $msg = "تنبيه: توجد فاتورة مستحقة السداد.\n"
            . "رقم الفاتورة: {$number}\n"
            . ($due ? "تاريخ الاستحقاق: {$due}\n" : '')
            . $lineInfo
            . $noteSnip;
            
        try {
            $resp = $twilio->sendSms($targetPhone, $msg);

            InvoiceReminderLog::create([
                'invoice_id' => $invoice->id,
                'rule' => $rule,
                'run_date' => $runDate,
                'channel' => 'sms',
                'status' => 'sent',
                'response' => is_string($resp) ? $resp : json_encode($resp, JSON_UNESCAPED_UNICODE),
            ]);

            Log::info('[REM] SMS sent', [
                'invoice_id' => $invoice->id,
                'to' => $targetPhone,
                'rule' => $rule,
                'from_field' => $targetLabel,
            ]);

            return true;
        } catch (\Throwable $e) {
            report($e);
            InvoiceReminderLog::create([
                'invoice_id' => $invoice->id,
                'rule' => $rule,
                'run_date' => $runDate,
                'channel' => 'sms',
                'status' => 'failed',
                'response' => $e->getMessage(),
            ]);
            Log::error('[REM] SMS failed', [
                'invoice_id' => $invoice->id,
                'to' => $targetPhone,
                'rule' => $rule,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * تبسيط/تطبيع رقم الهاتف (اختياري؛ عدّل حسب بلدك).
     * هنا فقط نزيل المسافات والداش ونسمح بقيادة +… أو 0… كما هي.
     */
    private function normalizeMsisdn(?string $raw): ?string
    {
        if (!$raw)
            return null;

        // نظّف أي رموز غير الأرقام وعلامة +
        $s = preg_replace('/[^\d\+]/u', '', trim($raw));

        // 1) رقم بصيغة محلية يبدأ بـ 0  => +970 ثم باقي الرقم بدون الـ 0
        if (preg_match('/^0\d+$/', $s)) {
            return '+970' . substr($s, 1);
        }

        // 2) لو كان مكتوبًا مسبقًا كـ 972xxxxxxxx (بدون +)
        if (preg_match('/^972\d+$/', $s)) {
            // تأكد ما في صفر زائد بعد 972
            $rest = ltrim(substr($s, 3), '0');
            return '+970' . $rest;
        }

        // 3) لو كان مكتوبًا مسبقًا كـ +970xxxxxxxx
        if (str_starts_with($s, '+970')) {
            // احذف أي صفرٍ مباشرة بعد 972 إن وجد
            $rest = ltrim(substr($s, 4), '0');
            return '+970' . $rest;
        }

        // 4) أي حالة أخرى (نادرة): اعتبره محلي يبدأ بـ 0؟ إن لم يبدأ، أضف +970 كافتراضي
        if (preg_match('/^\d+$/', $s)) {
            // لو ما في صفر في البداية، اعتبره محلي وأضف +970 كما هو
            return '+970' . ltrim($s, '0');
        }

        return null;
    }

}
