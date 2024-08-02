<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ManageUserController;
use App\Http\Controllers\THM\PostController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/submissions', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('create', [PostController::class, 'createSubmission']);
    Route::get('', [PostController::class, 'getPosts']);
    Route::get('cover-posts', [PostController::class, 'getCoverPosts']);
    Route::get('{slug}', [PostController::class, 'getPostBySlug']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('create', [PostController::class, 'createPost']);
        Route::put('{post_id}/update', [PostController::class, 'updatePost']);
    });
});
