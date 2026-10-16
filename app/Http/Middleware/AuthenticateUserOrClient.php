<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateUserOrClient
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next): Response
    {
        $debugEnabled = false;

        if($debugEnabled){
            _debug('attempt Auth:api');
        }

        if (Auth::guard('api')->check()) {
            Auth::shouldUse('api');
            return $next($request);
        }

        if($debugEnabled){
            _debug('Auth:api failed... now checking client credentials.');
        }

        return app(AuthenticateClientCredentials::class)->handle($request, $next);
    }
}
