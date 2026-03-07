<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, '__invoke']);
Route::get('/contact', [ContactController::class, '__invoke']);


Route::resource('tag', TagController::class);

Route::get('/signup',[AuthController::class,'showsignup'])->name('signup');
Route::post('/signup',[AuthController::class,'signup']);
Route::get( '/login',[AuthController::class,'showlogin'])->name('login');
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->name('logout');

Route::middleware('auth')->group(function(){

// ##Prinsaple of least Access privilege
// ##Admin can create, edit, delete posts
// ##Editor can edit posts
// ##Viewer can only view posts
Route::middleware('role:admin')->group(function(){
        Route::get('/post/create',[PostController::class,'create']);
        Route::post('/post',[PostController::class,'store']);
        Route::delete('/post/{post}',[PostController::class,'destroy']);
    });

    // ##Admin, Editor can view posts
Route::middleware('role:viewer,editor,admin')->group(function(){
    Route::get('/post',[PostController::class,'index']);
    Route::get('/post/{post}',[PostController::class,'show']);
});

// ##Admin, Editor can edit posts
Route::middleware('role:editor,admin')->group(function(){
    // ##Using Gates
    // ## ##Using Policies
 Route::middleware('can:update,post')->group(function(){
            Route::get('/post/{post}/edit',[PostController::class,'edit']);
            Route::patch('/post/{post}',[PostController::class,'update']);
    });


Route::resource('comment', CommentController::class);//comment

});

});

Route::middleware('onlyme')->group(function(){
Route::get('/about', AboutController::class);
});









