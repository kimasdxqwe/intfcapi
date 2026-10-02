<?php

namespace App\Http\Controllers;

use App\Blueprint\RequestInterface;
use App\Facades\ResponseJson;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function index(Request $request)
    {
        if($request->expectsJson())
        {
            $requestInterface = app(RequestInterface::class);

            //$requestInterface->debug('DebugController');

            $requestIsFromApiEndpoint = $request->is('api/*');
            $requestAcceptsJson = $request->expectsJson();

            _debug([
                'Request is from api endpoint' => $requestIsFromApiEndpoint,
                'Request accept json' => $requestAcceptsJson,
            ]);

            return ResponseJson::successfulResponse([
                'RequestConcrete oauth' => [
                    'resolved' => $requestInterface->oAuthContext->resolved,
                    'route' => [
                        'path' => request()->path(),
                        'scope?' => $requestInterface->oAuthContext->scopeFlag ? 'scope' : 'scopes',
                        'scope params' => $requestInterface->oAuthContext->scopes,
                    ],
                    'token' => [
                        'token instance' => get_class($requestInterface->oAuthContext?->accessToken),
                        ...($requestInterface->oAuthContext->resolved
                            ? $requestInterface->oAuthContext->accessToken?->toArray()
                            : []
                        )
                    ],
                ]
            ]);
        }

        abort(404);
    }
}
