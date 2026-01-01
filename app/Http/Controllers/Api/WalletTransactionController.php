<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WalletTransactionsIndexRequest;
use App\Http\Resources\Api\WalletTransactionResource;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    public function index(WalletTransactionsIndexRequest $request)
    {
        $user = $request->user();
        if (!$user) return api_error('Unauthenticated', 401);

        $q = WalletTransaction::query()
            ->where('user_id', $user->id)
            ->with(['payment:id,method'])
            ->orderByDesc('created_at');

        if ($request->filled('direction')) $q->where('direction', $request->input('direction'));
        if ($request->filled('type')) $q->where('type', $request->input('type'));
        if ($request->filled('status')) $q->where('status', $request->input('status'));

        $p = $q->paginate(50);
        $p->setCollection($p->getCollection()->map(fn($t) => new WalletTransactionResource($t)));

        return api_paginated($p);
    }
}