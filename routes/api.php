<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepositController;

Route::prefix('')->group(function () {
    Route::get('/deposit/lasts', [DepositController::class, 'lasts']);
    Route::get('/transfer/lasts', [TransferController::class, 'lasts']);

    Route::apiResource('deposit', DepositController::class);
    Route::apiResource('transfer', TransferController::class);
    Route::apiResource('user', UserController::class);
});
