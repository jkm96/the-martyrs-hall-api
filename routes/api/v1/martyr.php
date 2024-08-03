<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ManageSubmissionsController;
use App\Http\Controllers\TMH\MartyrController;
use App\Http\Controllers\TMH\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/martyrs-hall', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::get('', [MartyrController::class, 'getMartyrs']);
    Route::get('{slug}', [MartyrController::class, 'getMartyrBySlug']);
});
