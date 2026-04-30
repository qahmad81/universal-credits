<?php

namespace App\Http\Middleware;

use App\Models\VendorToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateVendorToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $tokenHash = hash('sha256', $token);

        $vendorToken = VendorToken::where('token_hash', $tokenHash)
            ->where('is_active', true)
            ->first();

        if (!$vendorToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->attributes->set('vendor_token', $vendorToken);

        return $next($request);
    }
}
