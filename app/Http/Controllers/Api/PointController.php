<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PointsRedeemRequest;
use App\Http\Resources\Api\PointTransactionResource;
use App\Services\PointsService;
use Illuminate\Http\Request;

class PointController extends Controller
{
    /**
     * GET /api/v1/points
     * صفحة النقاط: رصيد + النسبة + هل يمكن الاستبدال
     */
    public function show(Request $request, PointsService $pointsService)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        $wallet = $pointsService->ensureWallet($user);

        $rule = $pointsService->getRedeemRule();
        $balancePoints = (int) $wallet->balance_points;
        $balanceValue = $pointsService->balanceValue($balancePoints);

        $canRedeem = $balancePoints >= (int) $rule['min_redeem_points'];

        return api_success([
            'balance_points' => $balancePoints,
            'balance_value' => (string) $balanceValue,
            'currency' => $rule['currency'],

            'redeem_rule' => [
                'points' => (int) $rule['redeem_points'],
                'amount' => (string) $rule['redeem_amount'],
                'currency' => $rule['currency'],
                'min_redeem_points' => (int) $rule['min_redeem_points'],
            ],

            'can_redeem' => $canRedeem,
        ]);
    }

    public function previewRedeem(PointsRedeemRequest $request, PointsService $pointsService)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        $points = $request->input('points'); // nullable => all

        $data = $pointsService->previewRedeem($user, $points);

        return api_success($data, 'Redeem preview');
    }

    /**
     * GET /api/v1/points/transactions
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        $type = $request->input('type'); // earn|redeem|adjust|refund

        $q = $user->pointTransactions()
            ->where('is_archived', false)
            ->orderByDesc('id');

        if (in_array($type, ['earn', 'redeem', 'adjust', 'refund'])) {
            $q->where('type', $type);
        }

        $paginator = $q->paginate(50);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn($row) => new PointTransactionResource($row))
        );

        return api_paginated($paginator);
    }

    /**
     * POST /api/v1/points/redeem
     * body: {points?: 800}
     */
    public function redeem(PointsRedeemRequest $request, PointsService $pointsService)
    {
        $user = $request->user();
        if (!$user)
            return api_error('Unauthenticated', 401);

        $points = $request->input('points'); // nullable => all

        $result = $pointsService->redeemToWallet($user, $points, $user->id);

        if (!$result['ok']) {
            return api_validation_error($result['errors'], $result['message']);
        }

        return api_success([
            'money_amount' => (string) $result['money_amount'],
            'currency' => $result['currency'],
            'balance_points' => (int) $result['wallet']->balance_points,
            'transaction' => new PointTransactionResource($result['transaction']),
        ], 'Points redeemed successfully');
    }
}
