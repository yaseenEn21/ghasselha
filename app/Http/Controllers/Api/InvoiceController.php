<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InvoicesIndexRequest;
use App\Http\Resources\Api\InvoiceResource;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    /**
     * GET /api/v1/invoices
     */
    public function index(InvoicesIndexRequest $request)
    {
        $user = $request->user();
        if (!$user) return api_error('Unauthenticated', 401);

        $q = Invoice::query()
            ->where('user_id', $user->id)
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->withCount('items')
            ->withSum(['payments as paid_amount' => function ($pq) {
                $pq->where('status', 'paid');
            }], 'amount');

        if ($request->filled('status')) {
            $q->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $q->where('type', $request->input('type'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $q->where('number', 'like', "%{$search}%");
        }

        $paginator = $q->paginate(50);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn($inv) => new InvoiceResource($inv))
        );

        return api_paginated($paginator);
    }

    /**
     * GET /api/v1/invoices/{invoice}
     */
    public function show($invoiceId)
    {
        $user = request()->user();
        if (!$user) return api_error('Unauthenticated', 401);

        $invoice = Invoice::query()
            ->where('id', $invoiceId)
            ->where('user_id', $user->id)
            ->with([
                'items',
                'payments' => fn($q) => $q->orderByDesc('id'),
            ])
            ->first();

        if (!$invoice) {
            return api_error('Not found', 404);
        }

        return api_success(new InvoiceResource($invoice));
    }
}