<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
public function login(LoginRequest $request)
{
    $credentials = $request->only('email', 'password');
/** @var \Tymon\JWTAuth\JWTGuard $guard */

$guard = Auth::guard('api');

if (!$token = $guard->attempt($credentials)) {
    return response()->json(['message' => 'Unauthorized'], 401);
}

return response()->json([
    'access_token' => $token,
    'expires_in' => $guard->factory()->getTTL() * 60,
]);
}

public function refresh(LoginRequest $request)
{
    /** @var \Tymon\JWTAuth\JWTGuard $guard */
    $guard = Auth::guard('api');

    try {
        $newToken = $guard->refresh();
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['message' => 'Invalid token'], 401);
    }

    return response()->json([
        'access_token' => $newToken,
        'expires_in' => $guard->factory()->getTTL() * 60,
    ]);
}
public function myprofile(Request $request)
{
    return response()->json($request->user());
}



public function logout(LoginRequest $request)
{
    Auth::guard('api')->logout(true);
    return response()->json(['message' => 'Successfully logged out']);
}
}
