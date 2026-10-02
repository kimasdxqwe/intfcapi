<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\DummyController::class, 'index']);

if(!app()->environment('production', 'prod')) {

    Route::get('/php-info', function () {
        phpinfo();
    });
}

use App\Http\Controllers\AuthenticationController;

Route::get('/login', [AuthenticationController::class, 'index'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [AuthenticationController::class, 'login'])
    ->name('login.attempt')
    ->middleware('guest');

Route::post('/logout', [AuthenticationController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::view('/{any}', 'app')
    ->where('any', '^(?!api|build|storage).*$');
