<?php

namespace App\Http\Controllers;

use App\Blueprint\RequestInterface;
use App\Facades\ResponseJson;
use Illuminate\Http\Request;

class OAuthContextController extends Controller
{
    public function __invoke(Request $request)
    {
        if($request->expectsJson())
        {
            return ResponseJson::successfulResponse(
                app(RequestInterface::class)->oAuthContextPresenter()
            );
        }

        abort(404);
    }
}
