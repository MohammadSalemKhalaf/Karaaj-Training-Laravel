
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\PostApiController;
use App\Http\Controllers\api\v1\CommentApiController;
use App\Http\Controllers\api\v1\TagApiController;
use App\Http\Controllers\api\v1\AuthController;


Route::apiResource('comment', CommentApiController::class);
Route::apiResource('tag', TagApiController::class);

Route::prefix('v1')->group(function () {
    Route::apiResource('post', PostApiController::class)->middleware('auth:api');

    Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/myprofile', [AuthController::class, 'myprofile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
})
;
