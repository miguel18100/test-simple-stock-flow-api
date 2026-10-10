<?php

declare(strict_types=1);

use App\Presentation\Http\Controller\LoginController;
use App\Presentation\Http\Controller\PlaceSaleController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', LoginController::class);

Route::get('/diag-jwt', function () {
    return response(
        'keyLength=' . strlen((string) config('jwt.signing_key'))
        . '; envLength=' . strlen((string) env('JWT_SIGNING_KEY', '')),
        200,
        ['Content-Type' => 'text/plain']
    );
});

Route::middleware('jwt')->group(function (): void {
    Route::post('/sales', PlaceSaleController::class);
});