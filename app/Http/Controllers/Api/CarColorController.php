<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CarColorController extends Controller
{
    public function index(Request $request)
    {
        $items = [];
        foreach (car_color_map() as $key => $hex) {
            $items[] = [
                'key' => $key,
                'label' => __('colors.' . $key),
                'hex' => $hex,
            ];
        }

        return api_success(['items' => $items]);
    }
}