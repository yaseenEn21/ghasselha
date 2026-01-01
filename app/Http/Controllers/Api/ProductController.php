<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $lang = request_lang();

        $q = Product::query()
            ->where('is_active', true)
            ->when($request->filled('product_category_id'), function ($qq) use ($request) {
                $qq->where('product_category_id', $request->integer('product_category_id'));
            })
            ->with(['category:id,name,is_active'])
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $s = trim((string) $request->input('q'));
            $q->where(function ($qq) use ($s, $lang) {
                $qq->where("name->$lang", 'like', "%{$s}%")
                   ->orWhere("name->en", 'like', "%{$s}%");
            });
        }

        $p = $q->paginate(50);
        $p->setCollection($p->getCollection()->map(fn($i) => new ProductResource($i)));

        return api_paginated($p);
    }

    public function show(Request $request, Product $product)
    {
        if (!$product->is_active) {
            return api_error(__('api.not_found'), 404);
        }

        return api_success(new ProductResource($product));
    }
}

