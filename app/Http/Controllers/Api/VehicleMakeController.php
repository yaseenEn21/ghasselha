<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehicleMakeController extends Controller
{
    public function index(Request $request)
    {
        $lang = request_lang();
        $q = \App\Models\VehicleMake::query()
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

        if ($request->boolean('all')) {
            return api_success(\App\Http\Resources\Api\VehicleMakeResource::collection($q->get()));
        }

        $p = $q->paginate(50);
        $p->setCollection($p->getCollection()->map(fn($i) => new \App\Http\Resources\Api\VehicleMakeResource($i)));
        return api_paginated($p);
    }
}
