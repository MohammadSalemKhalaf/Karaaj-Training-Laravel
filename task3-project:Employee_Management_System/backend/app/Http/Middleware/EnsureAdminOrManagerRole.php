<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrManagerRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('api');

        if (! $user) {
            return ApiResponse::error(
                'Unauthenticated.',
                ['auth' => ['Authentication token is missing or invalid.']],
                'AUTH_UNAUTHENTICATED',
                401
            );
        }

        $user->loadMissing('role');

        if (! in_array($user->role?->name, ['admin', 'manager'], true)) {
            return ApiResponse::error(
                'Forbidden. Admin or manager role is required.',
                ['authorization' => ['You are not allowed to perform this action.']],
                'AUTH_FORBIDDEN',
                403
            );
        }

        return $next($request);
    }
}
