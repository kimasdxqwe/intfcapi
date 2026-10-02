<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserOrClientOwner
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next): Response
    {
        _debug('EnsureUserOrClientOwner');

        _debug('attempt Auth:api');

        // Try user-scoped auth first (authorization code / PKCE tokens)
        if (Auth::guard('api')->check()) {
            return $next($request);
        }

        _debug('Auth:api failed... now checking client credentials.');

        // Fall back to client-credentials / resource-owner check.
        // Delegate to the existing middleware's own handle() logic so
        // you don't duplicate its internals here.
        return app(EnsureClientIsCustomOwner::class)->handle($request, $next);
    }
}
