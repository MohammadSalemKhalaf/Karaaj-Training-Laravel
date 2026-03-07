<?php

namespace App\Http\Middleware;
use Illuminate\Http\Request;
use Closure;
use App\Services\CustomTokenService;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        $tokenService = app(CustomTokenService::class);
        $user = $tokenService->validateToken($token);

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        $request->user = $user;

        return $next($request);
    }
}
