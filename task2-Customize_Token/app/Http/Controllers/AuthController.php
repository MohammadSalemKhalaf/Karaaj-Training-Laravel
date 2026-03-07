<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use App\Services\CustomTokenService;
use Hash;
use App\Events\TokenCreated;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function login(LoginRequest $request , CustomTokenService $tokenService)
    {
        
       $user = User::where('email', $request->email)->first();
       if(!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
       }

        $token = $tokenService->createToken($user);
        if($token) {
            event(new TokenCreated($user->id));
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ]);
    }
}
