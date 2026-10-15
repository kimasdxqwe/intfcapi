<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;

if(!app()->environment('production', 'prod')) {

    Route::get('/php-info', function () {
        phpinfo();
    });

    Route::group([
        'middleware' => ['auth']
    ], function () {

        Route::post('/test-post', function (Request $request){
            return $request->user();
        });

        Route::patch('/test-patch/{identifier}', function (Request $request, $identifier){
            return ['identifier'=> $identifier];
        });

        Route::delete('/test-delete/{identifier}', function (Request $request, $identifier){
            return ['identifier'=> $identifier];
        });
    });
}

Route::view('/', 'welcome');

Route::group([
    'middleware' => ['auth']
], function () {

    Route::get('/user', fn (Request $request) => $request->user());

    Route::post('/logout', [AuthenticationController::class, 'logout'])
        ->name('logout');
});

Route::group([
    'middleware' => ['guest']
], function () {

    Route::get('/login', [AuthenticationController::class, 'index'])
        ->name('login');

    Route::post('/login', [AuthenticationController::class, 'login'])
        ->name('login.attempt');
});

Route::view('/{any}', 'app')
    ->where('any', '^(?!api|build|storage).*$');
