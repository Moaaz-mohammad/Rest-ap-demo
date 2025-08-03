<?php

use App\Http\Controllers\Api\NotificationControllere;
use App\Http\Controllers\Api\Tagcontroller;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostReaportController;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::post('/posts/{id}/like', [PostController::class, 'like']);
    Route::delete('/posts/{id}/unlike', [PostController::class, 'unlike']);
    Route::apiResource('comments', CommentController::class);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('tags', Tagcontroller::class);

    Route::get('/notifications', [NotificationControllere::class, 'index']);
    
    Route::post('/notifications/{id}/read', [NotificationControllere::class, 'markAsRead']);

    Route::post('/notifications/all-read', [NotificationControllere::class, 'markAllRead']);

    Route::delete('/notifications/{id}', [NotificationControllere::class, 'destroy']);

    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/posts/{post}/bookmark', [BookmarkController::class, 'store']);
    Route::delete('/posts/{post}/bookmark', [BookmarkController::class, 'destroy']);

    Route::post('/posts/{id}/archive', [PostController::class, 'archive']);

    // Report
    Route::post('/posts/report', [PostReaportController::class, 'store']);
});

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

