<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentsIndexRequest;
use App\Http\Resources\Api\PaymentResource;
use App\Models\Payment;

class PaymentController extends Controller
{
    /**
     * GET /api/v1/payments
     */
    public function index(PaymentsIndexRequest $request)
    {
        $user = $request->user();
        if (!$user) return api_error('Unauthenticated', 401);

        $q = Payment::query()
            ->where('user_id', $user->id)
            ->with(['invoice:id,number,type,status,total'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $q->where('status', $request->input('status'));
        }

        if ($request->filled('method')) {
            $q->where('method', $request->input('method'));
        }

        if ($request->filled('invoice_id')) {
            $q->where('invoice_id', (int) $request->input('invoice_id'));
        }

        $paginator = $q->paginate(50);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn($p) => new PaymentResource($p))
        );

        return api_paginated($paginator);
    }

    /**
     * GET /api/v1/payments/{payment}
     */
    public function show($paymentId)
    {
        $user = request()->user();
        if (!$user) return api_error('Unauthenticated', 401);

        $payment = Payment::query()
            ->where('id', $paymentId)
            ->where('user_id', $user->id)
            ->with(['invoice:id,number,type,status,total'])
            ->first();

        if (!$payment) return api_error('Not found', 404);

        return api_success(new PaymentResource($payment));
    }
}
