<?php

namespace App\Http\Controllers;

use App\Services\TwilioService;
use App\Services\SMSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TwilioTestController extends Controller
{
    /**
     * تست + Twilio (عن طريق API عام)
     */
    public function __invoke(Request $request, TwilioService $twilio): JsonResponse
    {
        $request->validate([
            'to' => ['required', 'string'],
            'message' => ['required', 'string', 'max:1600'],
        ]);

        if (App::isProduction()) {
            abort(403, 'Twilio test route is disabled in production.');
        }

        try {
            $result = $twilio->sendSms($request->input('to'), $request->input('message'));

            return response()->json([
                'message' => 'Test SMS dispatched via Twilio.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Failed to send SMS via Twilio.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * مثال ثابت لـ Twilio
     */
    public function sendX(TwilioService $twilio): JsonResponse
    {
        if (App::isProduction()) {
            abort(403, 'Twilio test route is disabled in production.');
        }

        $result = $twilio->sendSms('+970595587368', 'مرحبا');

        return response()->json([
            'message' => 'Test SMS via Twilio (hardcoded).',
            'data' => $result,
        ]);
    }

    public function send(SMSService $sms)
    {
        $carNumber = "012345678";
        $expiryDate = "01/01/2026";

        // $ins_message = "زبوننا الكريم\n"
        //     . "ننوهكم ان تامين سيارتكم رقم {$carNumber} ينتهي بتاريخ {$expiryDate}\n"
        //     . "للحصول على افضل سعر لتجديد التامين يرجى ارسال رخصة المركبة + رخصه اصغر سائق من الوجهين على الواتس اب رقم 0522371121";

        $golan_message =  "زبوننا الكريم ، يجب تسديد فاتورة الهاتف خلال 24 ساعة تفاديا لفصل الخط\n"
             . "جولان تلكوم بلدي مول\n"
             . "ساعات الدوام من 9 صباحا حتى ال 7 مساءاً";

        return $sms->send(
            "0586990999",
            $golan_message,
            type: 0
        );
    }


}