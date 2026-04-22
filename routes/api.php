<?php

use App\Http\Controllers\Api\ArticleController;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'v1'], function () {
    
    // Auth
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::group(['middleware' => 'auth:sanctum'], function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // Public
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}/related', [ArticleController::class, 'related']);
    Route::get('/articles/{id}/navigation', [ArticleController::class, 'navigation']);
    Route::get('/articles/{slug}', [ArticleController::class, 'show']);
    Route::post('/articles/{id}/like', [ArticleController::class, 'like']);
    Route::delete('/articles/{id}/like', [ArticleController::class, 'unlike']);
    Route::post('/articles/{id}/comments', [\App\Http\Controllers\Api\CommentController::class, 'store']);
    Route::get('/articles/{id}/comments', [\App\Http\Controllers\Api\CommentController::class, 'showByArticle']);
    
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{slug}', [EventController::class, 'show']);
    Route::post('/events/{id}/like', [EventController::class, 'like']);
    Route::delete('/events/{id}/like', [EventController::class, 'unlike']);
    Route::post('/events/{id}/comments', [\App\Http\Controllers\Api\CommentController::class, 'storeEventComment']);
    Route::get('/events/{id}/comments', [\App\Http\Controllers\Api\CommentController::class, 'showByEvent']);

    Route::post('/newsletter/subscribe', [\App\Http\Controllers\Api\NewsletterController::class, 'subscribe']);
    Route::post('/newsletter/unsubscribe', [\App\Http\Controllers\Api\NewsletterController::class, 'unsubscribe']);

    Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'index']);
    Route::get('/settings', [\App\Http\Controllers\Api\SettingsController::class, 'index']);
    Route::post('/contact', [\App\Http\Controllers\Api\ContactController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
        Route::put('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
        Route::delete('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'destroy']);
    });
    
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{slug}', [TagController::class, 'show']);

    // Protected Admin
    Route::group(['middleware' => ['auth:sanctum', 'admin.only']], function () {

        
        Route::group(['prefix' => 'admin'], function () {
            // Articles
            Route::post('/articles', [ArticleController::class, 'store']);
            Route::put('/articles/{article}', [ArticleController::class, 'update']);
            Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
            
            // Events
            Route::post('/events', [EventController::class, 'store']);
            Route::put('/events/{event}', [EventController::class, 'update']);
            Route::delete('/events/{event}', [EventController::class, 'destroy']);

            // Media
            Route::get('/media', [MediaController::class, 'index']);
            Route::get('/media/{media}', [MediaController::class, 'show']);
            Route::post('/media/upload', [MediaController::class, 'upload']);
            Route::put('/media/{media}', [MediaController::class, 'update']);
            Route::delete('/media/{media}', [MediaController::class, 'destroy']);

            // Comments (Admin)
            Route::get('/comments', [\App\Http\Controllers\Api\CommentController::class, 'index']);
            Route::put('/comments/{id}/approve', [\App\Http\Controllers\Api\CommentController::class, 'approve']);
            Route::delete('/comments/{id}', [\App\Http\Controllers\Api\CommentController::class, 'destroy']);

            // Newsletter (Admin)
            Route::get('/newsletter/subscribers', [\App\Http\Controllers\Api\NewsletterController::class, 'index']);

            // Settings (Admin)
            Route::put('/settings', [\App\Http\Controllers\Api\SettingsController::class, 'update']);

            // Categories & Tags
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{category}', [CategoryController::class, 'update']);
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
            Route::post('/tags', [TagController::class, 'store']);
            Route::put('/tags/{tag}', [TagController::class, 'update']);
            Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
        });

    });

});

