<?php

use App\Http\Controllers\TMH\SiteContentController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/site-content', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('retrieve', [SiteContentController::class, 'getSiteContent']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('create', [SiteContentController::class, 'createContent']);
        Route::post('retrieve-by-id', [SiteContentController::class, 'getSiteContentById']);
        Route::post('update', [SiteContentController::class, 'updateSiteContent']);
    });
});
