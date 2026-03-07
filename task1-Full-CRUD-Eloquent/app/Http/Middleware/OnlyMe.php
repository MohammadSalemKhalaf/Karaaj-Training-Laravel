<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class OnlyMe
{
  

public function handle(Request $request, Closure $next): Response
{
    if (Auth::check()) {

        if (Auth::user()->email === 'kaseel134@gmail.com') {
            return $next($request);
        }

        return response(['message' => 'You are not authorized'], 403);
    }

    return response(['message' => 'You are not logged in'], 401);
}
}
