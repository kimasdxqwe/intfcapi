<?php

use App\Http\Controllers\OAuthContextController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth_user_or_client',
    'user_scoped_auth',
    'scope:user:read'
])->get('/user', fn (Request $request) => $request->user());

Route::group([
    'middleware' => [
        'auth_user_or_client'
    ],
    'prefix' => 'v1'
], function(){

    Route::group([
        'middleware' => [
            'scope:wms:interfacing'
        ]
    ], function(){

        Route::get('/oauth-context', OAuthContextController::class)->name('oauth-context');
    });
});


