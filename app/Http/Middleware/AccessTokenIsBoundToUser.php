<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenIsBoundToUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->guard('api')->user();

        abort_if(! $user, Response::HTTP_FORBIDDEN, 'This endpoint requires a user access token.');

        return $next($request);
    }
}
