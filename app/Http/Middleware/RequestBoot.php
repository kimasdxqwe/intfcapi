<?php

namespace App\Http\Middleware;

use App\Blueprint\RequestInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestBoot
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
//        _debug('RequestBoot');

        $setRefreshTokenIntoTheRequestForm = $request->hasCookie('refresh_token') && !$request->has('refresh_token');

        _debug([
            'has refresh_token cookie' => $request->hasCookie('refresh_token'),
            'request has refresh_token' => $request->has('refresh_token'),
            'set refresh_token' => $setRefreshTokenIntoTheRequestForm,
            'refresh_token' => $request->cookie('refresh_token'),
        ]);

        if($setRefreshTokenIntoTheRequestForm){

            $refresh_token = $request->cookie('refresh_token');

            $request->request->set('refresh_token', $refresh_token);
        }

        $requestPayload = $request->get('payload');
        $requestPayload = is_string($requestPayload) ? json_decode($requestPayload) : (object)[];

        $requestFilters = $request->get('filters');
        $requestFilters = is_string($requestFilters) ? json_decode($requestFilters) : (object)[];

        $requestOrders = $request->get('orders');
        $requestOrders = is_string($requestOrders) ? json_decode($requestOrders) : (object)[];

        $requestInterface = app(RequestInterface::class);

//        $requestInterface->debug('RequestBoot Before');

        $requestInterface->payload = $requestPayload;
        $requestInterface->filters = $requestFilters;
        $requestInterface->orders = $requestOrders;

//        $requestInterface->debug('RequestBoot After');

        return $next($request);
    }
}
