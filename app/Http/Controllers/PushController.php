<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PushSubscription;

class PushController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint' => 'required|url',
            'keys.p256dh' => 'nullable|string',
            'keys.auth' => 'nullable|string',
        ]);

        $payload = $request->all();

        $sub = PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id' => $request->user()?->id ?? null,
                'public_key' => $data['keys']['p256dh'] ?? null,
                'auth_token' => $data['keys']['auth'] ?? null,
                'raw' => $payload,
            ]
        );

        return response()->json(['ok' => true, 'id' => $sub->id]);
    }
}
