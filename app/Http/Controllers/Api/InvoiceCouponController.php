<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InvoiceCouponRequest;
use App\Http\Resources\Api\InvoiceResource;
use App\Http\Resources\Api\PromotionCouponResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\CouponService;

class InvoiceCouponController extends Controller
{
    public function preview(InvoiceCouponRequest $request, Invoice $invoice, CouponService $couponService)
    {
        $user = $request->user();
        if (!$user) return api_error('Unauthenticated', 401);

        if ((int)$invoice->user_id !== (int)$user->id) {
            return api_error('Not found', 404);
        }

        $result = $couponService->preview($invoice, $user, $request->input('code'));

        if (!$result['ok']) {
            return api_validation_error($result['errors'], $result['message']);
        }

        return api_success([
            'invoice' => new InvoiceResource($invoice->load('items', 'payments')),
            'preview' => [
                'eligible_base' => (string) $result['eligible_base'],
                'gross_total' => (string) $result['gross_total'],
                'discount' => (string) $result['discount'],
                'total_after_discount' => (string) $result['total_after_discount'],
            ],
            'coupon' => new PromotionCouponResource($result['coupon']->load('promotion')),
        ], 'Coupon preview');
    }

    public function apply(InvoiceCouponRequest $request, Invoice $invoice, CouponService $couponService)
    {
        $user = $request->user();
        if (!$user) return api_error('Unauthenticated', 401);

        if ((int)$invoice->user_id !== (int)$user->id) {
            return api_error('Not found', 404);
        }

        $result = $couponService->apply($invoice, $user, $request->input('code'), $user->id);

        if (!$result['ok']) {
            return api_validation_error($result['errors'], $result['message']);
        }

        return api_success(new InvoiceResource($result['invoice']), 'Coupon applied successfully');
    }

    public function destroy(Request $request, Invoice $invoice, CouponService $couponService)
    {
        $user = $request->user();
        if (!$user) return api_error('Unauthenticated', 401);

        $result = $couponService->removeCoupon($invoice, $user, $user->id);

        if (!$result['ok']) {
            return api_validation_error($result['errors'], $result['message']);
        }

        return api_success(new InvoiceResource($result['invoice']), 'Coupon removed successfully');
    }
}