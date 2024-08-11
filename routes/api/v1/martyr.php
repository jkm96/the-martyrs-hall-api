<?php

use App\Http\Controllers\TMH\MartyrController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/martyrs-hall', 'namespace' => 'api/v1', 'middleware' => 'api'], function () {
    Route::post('', [MartyrController::class, 'getMartyrs']);
    Route::post('retrieve', [MartyrController::class, 'retrieveMartyrById']);
    Route::post('light-candle', [MartyrController::class, 'lightMartyrsCandle']);
});
