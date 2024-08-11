<?php

use App\Http\Controllers\TMH\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/submissions', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('create', [SubmissionController::class, 'createSubmission']);
    Route::get('', [SubmissionController::class, 'getPosts']);
    Route::get('cover-posts', [SubmissionController::class, 'getCoverPosts']);
    Route::get('{slug}', [SubmissionController::class, 'getPostBySlug']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::put('{post_id}/update', [SubmissionController::class, 'updatePost']);
    });
});
