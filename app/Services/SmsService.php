<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SMsService
{
    protected string $apiToken;
    protected string $apiUrl = "http://send.triple-core.com/sendbulksms.php";

    public function __construct()
    {
        $this->apiToken = config('services.smsservice.api_token');
    }

    /**
     * إرسال رسالة SMS عبر Triple-Core
     */
    public function send(
        string $to,
        string $message,
        string $sender = 'Ghasselha',
        int $type = 0
    ): array {

        $params = [
            'api_token' => $this->apiToken,
            'sender'    => $sender,
            'mobile'    => $to,
            'type'      => $type,
            'text'      => $message,
        ];

        // 📝 لوج قبل الإرسال
        Log::info('SMSService: preparing to send SMS', [
            'to'      => $to,
            'sender'  => $sender,
            'type'    => $type,
            'length'  => mb_strlen($message),
            'url'     => $this->apiUrl,
            'params'  => $params,
        ]);

        try {
            // Triple-Core uses GET only – نضيف timeout عشان ما يعلق
            $response = Http::timeout(15)->get($this->apiUrl, $params);
        } catch (Throwable $e) {
            // ❌ لو صار exception (مش قادر يوصل للسيرفر مثلاً)
            Log::error('SMSService: HTTP exception while sending SMS', [
                'to'        => $to,
                'sender'    => $sender,
                'url'       => $this->apiUrl,
                'params'    => $params,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'raw'     => null,
                'code'    => null,
                'message' => 'فشل الاتصال بخدمة الرسائل',
                'request' => $params,
            ];
        }

        $raw  = trim($response->body());
        $code = $this->extractCode($raw);
        $msg  = $this->translateCode($code);

        $result = [
            'success' => $response->successful(),
            'raw'     => $raw,
            'code'    => $code,
            'message' => $msg,
            'request' => $params,
            'http_status' => $response->status(),
        ];

        // 📝 لوج بعد الإرسال
        Log::info('SMSService: SMS send response', $result);

        return $result;
    }

    /** استخراج الكود من النص الخام */
    private function extractCode(string $raw): string
    {
        if (preg_match('/\d+/', $raw, $m)) {
            return $m[0];
        }
        return '';
    }

    /** ترجمة كود النتيجة */
    private function translateCode(string $code): string
    {
        return [
            '1001' => 'تم إرسال الرسالة بنجاح',
            '1000' => 'لا يوجد رصيد كافي',
            '2000' => 'خطأ في عملية التفويض',
        ][$code] ?? 'نتيجة غير معروفة';
    }
}