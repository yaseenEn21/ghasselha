<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehicleModelController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'vehicle_make_id' => ['required', 'exists:vehicle_makes,id'],
        ]);

        $lang = request_lang();
        $makeId = (int)$request->input('vehicle_make_id');

        $q = \App\Models\VehicleModel::query()
            ->where('vehicle_make_id', $makeId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('q')) {
            $s = trim((string)$request->input('q'));
            $q->where(function ($qq) use ($s, $lang) {
                $qq->where("name->$lang", 'like', "%{$s}%")
                   ->orWhere("name->en", 'like', "%{$s}%");
            });
        }

        $p = $q->paginate(50);
        $p->setCollection($p->getCollection()->map(fn($i) => new \App\Http\Resources\Api\VehicleModelResource($i)));
        return api_paginated($p);
    }
}