<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MyCarController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $q = \App\Models\Car::query()
            ->where('user_id', $user->id)
            ->with(['make:id,name', 'model:id,name,vehicle_make_id'])
            ->orderByDesc('is_default')
            ->orderByDesc('id');

        $p = $q->paginate(50);
        $p->setCollection($p->getCollection()->map(fn($i) => new \App\Http\Resources\Api\CarResource($i)));
        return api_paginated($p);
    }

    public function store(\App\Http\Requests\Api\CarStoreRequest $request)
    {
        $user = $request->user();

        $car = \DB::transaction(function () use ($request, $user) {
            $data = $request->validated();
            $data['user_id'] = $user->id;

            if (!empty($data['is_default'])) {
                \App\Models\Car::where('user_id', $user->id)->update(['is_default' => false]);
            }

            return \App\Models\Car::create($data);
        });

        $car->load(['make:id,name', 'model:id,name,vehicle_make_id']);

        return api_success(new \App\Http\Resources\Api\CarResource($car), __('cars.created'), 201);
    }

    public function show(Request $request, \App\Models\Car $car)
    {
        if ($car->user_id !== $request->user()->id) {
            return api_error(__('api.not_found'), 404);
        }

        $car->load(['make:id,name', 'model:id,name,vehicle_make_id']);
        return api_success(new \App\Http\Resources\Api\CarResource($car));
    }

    public function update(\App\Http\Requests\Api\CarUpdateRequest $request, \App\Models\Car $car)
    {
        if ($car->user_id !== $request->user()->id) {
            return api_error(__('api.not_found'), 404);
        }

        \DB::transaction(function () use ($request, $car) {
            $data = $request->validated();

            if (!empty($data['is_default'])) {
                \App\Models\Car::where('user_id', $car->user_id)->update(['is_default' => false]);
            }

            $car->update($data);
        });

        $car->refresh()->load(['make:id,name', 'model:id,name,vehicle_make_id']);
        return api_success(new \App\Http\Resources\Api\CarResource($car), __('cars.updated'));
    }

    public function destroy(Request $request, \App\Models\Car $car)
    {
        if ($car->user_id !== $request->user()->id) {
            return api_error(__('api.not_found'), 404);
        }

        $car->delete();
        return api_success(null, __('cars.deleted'));
    }
}