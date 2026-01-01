<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PackageSubscription;
use Illuminate\Http\Request;

class BookingPackageController extends Controller
{
    public function eligibility(Request $request)
    {
        $user = $request->user();
        if (!$user) return api_error('Unauthenticated', 401);

        $data = $request->validate([
            'service_id' => ['required','integer','exists:services,id'],
        ]);

        $serviceId = (int) $data['service_id'];

        $subs = PackageSubscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('remaining_washes', '>', 0)
            ->whereDate('ends_at', '>=', now()->toDateString())
            ->whereHas('package.services', fn($q) => $q->where('services.id', $serviceId))
            ->with(['package'])
            ->orderBy('ends_at')
            ->get();

        return api_success([
            'has_package' => $subs->isNotEmpty(),
            'items' => $subs->map(function($s){
                return [
                    'id' => $s->id,
                    'package_id' => $s->package_id,
                    'package_name' => $s->package ? i18n($s->package->name) : null,
                    'remaining_washes' => (int) $s->remaining_washes,
                    'starts_at' => $s->starts_at,
                    'ends_at' => $s->ends_at,
                ];
            }),
        ], 'Eligibility');
    }
}