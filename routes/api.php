<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;

Route::prefix('')->group(function () {
    Route::apiResource('user', UserController::class);
    Route::apiResource('transfer', TransferController::class)->only(['index', 'store', 'show']);
});
