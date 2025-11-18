<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureMerchantProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->role === 'mercerie' && (!$user->city || !$user->phone || !$user->address)) {
            // Si le profil est incomplet, on redirige avec un flag spécial
            return redirect()
                ->route('merchant.supplies.index')
                ->with('show_profile_modal', true);
        }

        return $next($request);
    }
}
