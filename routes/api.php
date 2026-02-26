<?php

use Illuminate\Support\Facades\Route;
use \Dock26Cookies\Http\Controllers\ApiController;
use \Dock26Cookies\Http\Middleware\AuthenticateAdmin;
use Dock26Cookies\Http\Middleware\VerifyNonce;

Route::middleware(AuthenticateAdmin::class)->prefix('dock26-cookies/v1')->group(function () {
    Route::get('/status', [ApiController::class, 'index']);
});
