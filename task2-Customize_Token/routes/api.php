<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;





Route::post('/login', [AuthController::class, 'login']);

Route::middleware('customAuth')->get('/profile', function (Request $request) {
    return response()->json($request->user);

    });