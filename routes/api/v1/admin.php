<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ManageSubmissionsController;
use App\Http\Middleware\CheckIsAdminMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/admin', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('register', [AdminController::class, 'registerAdmin']);
    Route::post('login', [AdminController::class, 'loginAdmin']);

    Route::middleware([Authenticate::using('sanctum'), CheckIsAdminMiddleware::class])->group(function () {
        Route::post('logout', [AdminController::class, 'logoutAdmin']);

        Route::group(['prefix' => 'manage-submissions', 'middleware' => 'api'], function () {
            Route::get('', [ManageSubmissionsController::class, 'getSubmissions']);
            Route::post('approve', [ManageSubmissionsController::class, 'approveSubmission']);
            Route::get('{userId}', [ManageSubmissionsController::class, 'getUserById']);
            Route::put('{userId}/toggle-status', [ManageSubmissionsController::class, 'toggleUserStatus']);
        });
    });
});
